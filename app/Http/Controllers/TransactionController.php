<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Services\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // 'items.product' di-eager-load karena tabel index sekarang nampilin
        // ringkasan nama produk & total qty per transaksi, bukan cuma nama kasir.
        $transactions = Transaction::with(['user', 'customer', 'items.product'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $todayQuery = Transaction::whereDate('created_at', today())->where('status', '!=', 'batal');

        $piutangTransactions = Transaction::where('status', 'piutang')
            ->withSum('payments', 'amount')
            ->get();

        $stats = [
            'today_count' => $todayQuery->count(),
            'today_omzet' => (clone $todayQuery)->sum('total'),
            'piutang_count' => $piutangTransactions->count(),
            'piutang_total' => $piutangTransactions->sum(fn ($t) => $t->total - ($t->payments_sum_amount ?? 0)),
        ];

        return view('transactions.index', compact('transactions', 'stats'));
    }

    public function create()
    {
        // ->active() : produk yang dinonaktifkan owner gak boleh muncul lagi
        // di katalog kasir, walaupun stoknya masih ada.
        $products = Product::with(['variants', 'ingredients'])
            ->active()
            ->where(function ($q) {
                $q->where('stock', '>', 0)
                    ->orWhere('has_variant', true)
                    ->orWhere('tracks_stock', false);
            })
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->get();

        return view('transactions.create', compact('products', 'customers'));
    }

    /**
     * Query produk aktif buat katalog kasir. Dipakai bareng-bareng
     * sama create() dan show() (pas mode "tambah item" ke invoice lama),
     * biar daftar produknya konsisten di kedua tempat.
     */
    private function catalogProducts()
    {
        return Product::with(['variants', 'ingredients'])
            ->active()
            ->where(function ($q) {
                $q->where('stock', '>', 0)
                    ->orWhere('has_variant', true)
                    ->orWhere('tracks_stock', false);
            })
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:150',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'additional_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:tunai,transfer,qris,lainnya',
            'paid_amount' => 'required|numeric|min:0',
            'is_piutang' => 'nullable|boolean',
        ]);

        // Normalisasi nama customer: string kosong dianggap null ("Umum")
        $customerName = trim((string) ($validated['customer_name'] ?? ''));
        $validated['customer_name'] = $customerName !== '' ? $customerName : null;

        $merged = [];
        foreach ($validated['items'] as $item) {
            $key = $item['product_id'] . '-' . ($item['product_variant_id'] ?? '0');
            if (isset($merged[$key])) {
                $merged[$key]['qty'] += $item['qty'];
            } else {
                $merged[$key] = $item;
            }
        }
        $validated['items'] = array_values($merged);

        $attempts = 0;

        do {
            $attempts++;

            try {
                $transaction = DB::transaction(function () use ($validated) {
                    $subtotal = 0;
                    $itemsData = [];

                    foreach ($validated['items'] as $item) {
                        if (!empty($item['product_variant_id'])) {
                            $variant = ProductVariant::lockForUpdate()->findOrFail($item['product_variant_id']);

                            if ((int) $variant->product_id !== (int) $item['product_id']) {
                                throw new \RuntimeException('Varian yang dipilih tidak sesuai dengan produknya.');
                            }

                            // Jaga-jaga race condition: kasir buka form pas produk masih
                            // aktif, lalu owner nonaktifkan produknya di tab lain sebelum
                            // kasir klik simpan. Cek ulang status produk induknya di sini,
                            // jangan cuma percaya data yang dikirim dari form.
                            $parentActive = Product::where('id', $variant->product_id)->value('is_active');
                            if (!$parentActive) {
                                throw new \RuntimeException('Produk untuk varian ini sudah tidak dijual lagi.');
                            }

                            if ($variant->stock < $item['qty']) {
                                throw new \RuntimeException("Stok varian {$variant->name} tidak mencukupi.");
                            }

                            $price = $variant->price_jual;
                        } else {
                            $product = Product::with('ingredients')->lockForUpdate()->findOrFail($item['product_id']);

                            if ($product->has_variant) {
                                throw new \RuntimeException("Produk {$product->name} punya varian, pilih variannya dulu.");
                            }

                            if (!$product->is_active) {
                                throw new \RuntimeException("Produk {$product->name} sudah tidak dijual lagi.");
                            }

                            if ($product->tracks_stock && $product->stock < $item['qty']) {
                                throw new \RuntimeException("Stok produk {$product->name} tidak mencukupi.");
                            }

                            foreach ($product->ingredients as $ingredient) {
                                $needed = $ingredient->pivot->qty_used * $item['qty'];
                                if ($ingredient->stock < $needed) {
                                    throw new \RuntimeException("Stok bahan {$ingredient->name} tidak mencukupi untuk {$product->name}.");
                                }
                            }

                            $price = $product->price_jual;
                        }

                        $lineSubtotal = $price * $item['qty'];
                        $subtotal += $lineSubtotal;

                        $itemsData[] = [
                            'product_id' => $item['product_id'],
                            'product_variant_id' => $item['product_variant_id'] ?? null,
                            'qty' => $item['qty'],
                            'price' => $price,
                            'subtotal' => $lineSubtotal,
                        ];
                    }

                    $discount = $validated['discount'] ?? 0;
                    $tax = $validated['tax'] ?? 0;
                    $additionalFee = $validated['additional_fee'] ?? 0;

                    if ($discount > $subtotal + $tax) {
                        throw new \RuntimeException('Diskon tidak boleh lebih besar dari subtotal ditambah pajak.');
                    }

                    $total = max(0, $subtotal - $discount + $tax + $additionalFee);
                    $paidAmount = $validated['paid_amount'];

                    // Semua metode bayar (tunai/transfer/qris/lainnya) dicatat manual oleh kasir.
                    $isPiutang = !empty($validated['is_piutang']) || $paidAmount < $total;
                    $status = $isPiutang ? 'piutang' : 'lunas';

                    $transaction = Transaction::create([
                        'invoice_number' => $this->generateInvoiceNumber(),
                        'user_id' => auth()->id(),
                        'customer_id' => $validated['customer_id'] ?? null,
                        'customer_name' => $validated['customer_name'],
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'tax' => $tax,
                        'additional_fee' => $additionalFee,
                        'total' => $total,
                        'payment_method' => $validated['payment_method'],
                        'status' => $status,
                        'paid_amount' => $paidAmount,
                        'change_amount' => max(0, $paidAmount - $total),
                    ]);

                    foreach ($itemsData as $data) {
                        $data['transaction_id'] = $transaction->id;
                        $transaction->items()->create($data);

                        if ($data['product_variant_id']) {
                            $variant = ProductVariant::lockForUpdate()->find($data['product_variant_id']);
                            $variant->decrement('stock', $data['qty']);
                            StockMovement::create([
                                'product_id' => $variant->product_id,
                                'product_variant_id' => $variant->id,
                                'type' => 'out',
                                'qty' => $data['qty'],
                                'note' => 'Penjualan ' . $transaction->invoice_number,
                                'user_id' => auth()->id(),
                            ]);
                        } else {
                            $product = Product::with('ingredients')->lockForUpdate()->find($data['product_id']);

                            if ($product->tracks_stock) {
                                $product->decrement('stock', $data['qty']);
                                StockMovement::create([
                                    'product_id' => $product->id,
                                    'type' => 'out',
                                    'qty' => $data['qty'],
                                    'note' => 'Penjualan ' . $transaction->invoice_number,
                                    'user_id' => auth()->id(),
                                ]);
                            }

                            foreach ($product->ingredients as $ingredient) {
                                $needed = $ingredient->pivot->qty_used * $data['qty'];

                                $ingredientLocked = Ingredient::lockForUpdate()->find($ingredient->id);
                                $ingredientLocked->decrement('stock', $needed);

                                IngredientStockMovement::create([
                                    'ingredient_id' => $ingredient->id,
                                    'type' => 'out',
                                    'qty' => $needed,
                                    'note' => 'Penjualan ' . $transaction->invoice_number . ' (' . $product->name . ')',
                                    'user_id' => auth()->id(),
                                ]);
                            }
                        }
                    }

                    if ($paidAmount > 0) {
                        Payment::create([
                            'transaction_id' => $transaction->id,
                            'amount' => min($paidAmount, $total),
                            'paid_at' => today(),
                            'payment_method' => $validated['payment_method'],
                            'note' => 'Pembayaran awal',
                        ]);
                    }

                    return $transaction;
                });

                return redirect()->route('transactions.show', $transaction)->with('success', 'Transaksi berhasil disimpan.');

            } catch (QueryException $e) {
                $isDuplicateInvoice = str_contains(strtolower($e->getMessage()), 'invoice_number');
                if (!$isDuplicateInvoice || $attempts >= 3) {
                    throw $e;
                }
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        } while ($attempts < 3);

        return back()->withInput()->with('error', 'Transaksi gagal disimpan, silakan coba lagi.');
    }

        /**
     * Generate invoice number secara atomic pakai tabel counter terpisah per tenant.
     * Format: INV-{tenant_id}-{Ymd}-{4 digit urut}. tenant_id dimasukkan ke
     * string-nya sendiri supaya nggak pernah collide dengan nomor lama
     * (format lama tanpa tenant_id) maupun dengan tenant lain, walau
     * angka urutnya kebetulan sama.
     */
    private function generateInvoiceNumber(): string
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            throw new \RuntimeException('Tidak ada tenant aktif, tidak bisa membuat transaksi.');
        }

        $today = today()->toDateString();

        DB::statement(
            'INSERT INTO invoice_counters (tenant_id, date, last_number, created_at, updated_at)
             VALUES (?, ?, LAST_INSERT_ID(1), NOW(), NOW())
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)',
            [$tenantId, $today]
        );

        $number = (int) DB::getPdo()->lastInsertId();

        return 'INV-' . $tenantId . '-' . now()->format('Ymd') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'items.variant', 'user', 'customer', 'payments' => fn ($q) => $q->oldest()]);

        // "Tambah Item" cuma dibolehin di hari yang sama transaksi dibuat &
        // transaksinya belum dibatalkan. Di luar itu, ngedit invoice yang
        // sudah lewat hari beresiko bikin rekap kas hari itu gak nyambung
        // lagi sama fisik uangnya.
        $canAddItems = $transaction->status !== 'batal' && $transaction->created_at->isToday();

        $products = $canAddItems ? $this->catalogProducts() : collect();

        return view('transactions.show', compact('transaction', 'canAddItems', 'products'));
    }

    /**
     * Tambah item ke transaksi yang sudah ada (dipakai buat kasus customer
     * pesan lagi setelah invoice pertama sudah lunas, di hari yang sama).
     * Bukan bikin invoice baru — item baru nempel ke invoice yang sama,
     * stok/bahan baku dipotong lagi, dan subtotal/total dihitung ulang.
     */
    public function addItems(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_method' => 'nullable|in:tunai,transfer,qris,lainnya',
            'additional_paid_amount' => 'nullable|numeric|min:0',
            'is_piutang' => 'nullable|boolean',
        ]);

        $merged = [];
        foreach ($validated['items'] as $item) {
            $key = $item['product_id'] . '-' . ($item['product_variant_id'] ?? '0');
            if (isset($merged[$key])) {
                $merged[$key]['qty'] += $item['qty'];
            } else {
                $merged[$key] = $item;
            }
        }
        $items = array_values($merged);

        try {
            DB::transaction(function () use ($items, $validated, $transaction) {
                // Lock baris transaksinya biar gak race sama request lain yang
                // juga lagi nambah item / bayar piutang di invoice yang sama.
                $transaction = Transaction::lockForUpdate()->findOrFail($transaction->id);

                if ($transaction->status === 'batal') {
                    throw new \RuntimeException('Transaksi ini sudah dibatalkan, tidak bisa ditambah item.');
                }

                if (!$transaction->created_at->isToday()) {
                    throw new \RuntimeException('Item cuma bisa ditambahkan di hari yang sama transaksi dibuat.');
                }

                $addedSubtotal = 0;

                foreach ($items as $item) {
                    if (!empty($item['product_variant_id'])) {
                        $variant = ProductVariant::lockForUpdate()->findOrFail($item['product_variant_id']);

                        if ((int) $variant->product_id !== (int) $item['product_id']) {
                            throw new \RuntimeException('Varian yang dipilih tidak sesuai dengan produknya.');
                        }

                        $parentActive = Product::where('id', $variant->product_id)->value('is_active');
                        if (!$parentActive) {
                            throw new \RuntimeException('Produk untuk varian ini sudah tidak dijual lagi.');
                        }

                        if ($variant->stock < $item['qty']) {
                            throw new \RuntimeException("Stok varian {$variant->name} tidak mencukupi.");
                        }

                        $price = $variant->price_jual;
                    } else {
                        $product = Product::with('ingredients')->lockForUpdate()->findOrFail($item['product_id']);

                        if ($product->has_variant) {
                            throw new \RuntimeException("Produk {$product->name} punya varian, pilih variannya dulu.");
                        }

                        if (!$product->is_active) {
                            throw new \RuntimeException("Produk {$product->name} sudah tidak dijual lagi.");
                        }

                        if ($product->tracks_stock && $product->stock < $item['qty']) {
                            throw new \RuntimeException("Stok produk {$product->name} tidak mencukupi.");
                        }

                        foreach ($product->ingredients as $ingredient) {
                            $needed = $ingredient->pivot->qty_used * $item['qty'];
                            if ($ingredient->stock < $needed) {
                                throw new \RuntimeException("Stok bahan {$ingredient->name} tidak mencukupi untuk {$product->name}.");
                            }
                        }

                        $price = $product->price_jual;
                    }

                    $lineSubtotal = $price * $item['qty'];
                    $addedSubtotal += $lineSubtotal;

                    $transaction->items()->create([
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'qty' => $item['qty'],
                        'price' => $price,
                        'subtotal' => $lineSubtotal,
                    ]);

                    if (!empty($item['product_variant_id'])) {
                        $variant = ProductVariant::lockForUpdate()->find($item['product_variant_id']);
                        $variant->decrement('stock', $item['qty']);
                        StockMovement::create([
                            'product_id' => $variant->product_id,
                            'product_variant_id' => $variant->id,
                            'type' => 'out',
                            'qty' => $item['qty'],
                            'note' => 'Tambahan item invoice ' . $transaction->invoice_number,
                            'user_id' => auth()->id(),
                        ]);
                    } else {
                        $product = Product::with('ingredients')->lockForUpdate()->find($item['product_id']);

                        if ($product->tracks_stock) {
                            $product->decrement('stock', $item['qty']);
                            StockMovement::create([
                                'product_id' => $product->id,
                                'type' => 'out',
                                'qty' => $item['qty'],
                                'note' => 'Tambahan item invoice ' . $transaction->invoice_number,
                                'user_id' => auth()->id(),
                            ]);
                        }

                        foreach ($product->ingredients as $ingredient) {
                            $needed = $ingredient->pivot->qty_used * $item['qty'];

                            $ingredientLocked = Ingredient::lockForUpdate()->find($ingredient->id);
                            $ingredientLocked->decrement('stock', $needed);

                            IngredientStockMovement::create([
                                'ingredient_id' => $ingredient->id,
                                'type' => 'out',
                                'qty' => $needed,
                                'note' => 'Tambahan item invoice ' . $transaction->invoice_number . ' (' . $product->name . ')',
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }

                $oldPaidAmount = $transaction->paid_amount;

                $newSubtotal = $transaction->subtotal + $addedSubtotal;
                $newTotal = max(0, $newSubtotal - $transaction->discount + $transaction->tax + $transaction->additional_fee);

                $additionalPaid = $validated['additional_paid_amount'] ?? 0;
                $newPaidAmount = $oldPaidAmount + $additionalPaid;

                // Sama kayak store(): kalau dibayar kurang dari total, otomatis piutang.
                $isPiutang = !empty($validated['is_piutang']) || $newPaidAmount < $newTotal;
                $status = $isPiutang ? 'piutang' : 'lunas';

                $transaction->update([
                    'subtotal' => $newSubtotal,
                    'total' => $newTotal,
                    'paid_amount' => $newPaidAmount,
                    'change_amount' => max(0, $newPaidAmount - $newTotal),
                    'status' => $status,
                ]);

                if ($additionalPaid > 0) {
                    // Dicap ke sisa yang masih kurang biar riwayat pembayaran gak
                    // pernah kelebihan dari total invoice, kembaliannya tetap
                    // kehandle lewat change_amount di atas.
                    $outstandingBefore = max(0, $newTotal - $oldPaidAmount);

                    Payment::create([
                        'transaction_id' => $transaction->id,
                        'amount' => $outstandingBefore > 0 ? min($additionalPaid, $outstandingBefore) : $additionalPaid,
                        'paid_at' => today(),
                        'payment_method' => $validated['payment_method'] ?? $transaction->payment_method,
                        'note' => 'Pembayaran tambahan item',
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('transactions.show', $transaction)->with('success', 'Item tambahan berhasil ditambahkan ke invoice ini.');
    }

    public function payPiutang(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'piutang') {
            return back()->with('error', 'Transaksi ini bukan piutang atau sudah lunas.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            // Metode bayar wajib diisi biar riwayat pembayaran piutang juga
            // kecatat lewat apa (tunai/transfer/qris/lainnya), sama kayak
            // pembayaran awal pas transaksi dibuat.
            'payment_method' => 'required|in:tunai,transfer,qris,lainnya',
            'note' => 'nullable|string',
        ]);

        $sisa = $transaction->sisaPiutang();

        if ($validated['amount'] > $sisa) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa piutang.');
        }

        DB::transaction(function () use ($validated, $transaction, $sisa) {
            Payment::create([
                'transaction_id' => $transaction->id,
                'amount' => $validated['amount'],
                'paid_at' => today(),
                'payment_method' => $validated['payment_method'],
                'note' => $validated['note'] ?? null,
            ]);

            if ($validated['amount'] >= $sisa) {
                $transaction->update(['status' => 'lunas']);
            }
        });

        return back()->with('success', 'Pembayaran piutang berhasil dicatat.');
    }

    public function cancel(Request $request, Transaction $transaction)
    {
        if ($transaction->status === 'batal') {
            return back()->with('error', 'Transaksi ini sudah dibatalkan sebelumnya.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $note = 'Pembatalan transaksi ' . $transaction->invoice_number
            . (!empty($validated['reason']) ? ' - ' . $validated['reason'] : '');

        DB::transaction(function () use ($transaction, $note) {
            $this->restoreStockAndCancel($transaction, $note);
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dibatalkan, stok sudah dikembalikan.');
    }

    protected function restoreStockAndCancel(Transaction $transaction, string $note): void
    {
        $transaction->load('items.product.ingredients', 'items.variant');

        foreach ($transaction->items as $item) {
            if ($item->product_variant_id) {
                $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

                if ($variant) {
                    $variant->increment('stock', $item->qty);
                    StockMovement::create([
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                        'type' => 'in',
                        'qty' => $item->qty,
                        'note' => $note,
                        'user_id' => auth()->id(),
                    ]);
                }
            } else {
                $product = Product::with('ingredients')->lockForUpdate()->find($item->product_id);

                if ($product) {
                    if ($product->tracks_stock) {
                        $product->increment('stock', $item->qty);
                        StockMovement::create([
                            'product_id' => $product->id,
                            'type' => 'in',
                            'qty' => $item->qty,
                            'note' => $note,
                            'user_id' => auth()->id(),
                        ]);
                    }

                    foreach ($product->ingredients as $ingredient) {
                        $restored = $ingredient->pivot->qty_used * $item->qty;

                        $ingredientLocked = Ingredient::lockForUpdate()->find($ingredient->id);
                        if ($ingredientLocked) {
                            $ingredientLocked->increment('stock', $restored);
                            IngredientStockMovement::create([
                                'ingredient_id' => $ingredient->id,
                                'type' => 'in',
                                'qty' => $restored,
                                'note' => $note . ' (' . $product->name . ')',
                                'user_id' => auth()->id(),
                            ]);
                        }
                    }
                }
            }
        }

        $transaction->update(['status' => 'batal']);
    }

    public function downloadPdf(Transaction $transaction)
    {
        $transaction->load(['items.product', 'items.variant', 'user', 'customer', 'payments' => fn ($q) => $q->oldest()]);
        $tenant = auth()->user()->tenant ?? null;

        $pdf = Pdf::loadView('transactions.pdf', compact('transaction', 'tenant'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('struk-' . $transaction->invoice_number . '.pdf');
    }
}