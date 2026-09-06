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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with(['user', 'customer'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
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
        // Produk boleh dipilih di kasir kalau:
        // - stoknya masih ada, ATAU
        // - punya varian (stok dicek di level varian), ATAU
        // - tracks_stock = false (produk kayak Es Teh, dibikin on-demand, gak ada batas stok produk)
        $products = Product::with(['variants', 'ingredients'])
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
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

        // Gabungkan baris item dengan produk/varian yang sama, akumulasikan qty-nya,
        // supaya pengecekan stok di bawah gak "lolos" gara-gara dicek per baris terpisah.
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
                            if ($variant->stock < $item['qty']) {
                                throw new \RuntimeException("Stok varian {$variant->name} tidak mencukupi.");
                            }

                            $price = $variant->price_jual;
                        } else {
                            $product = Product::with('ingredients')->lockForUpdate()->findOrFail($item['product_id']);

                            if ($product->has_variant) {
                                throw new \RuntimeException("Produk {$product->name} punya varian, pilih variannya dulu.");
                            }

                            // Produk tanpa tracks_stock (Es Teh dkk) skip pengecekan stok produk,
                            // tapi kalau dia punya resep bahan baku, bahan bakunya tetap harus dicek.
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
                    $isPiutang = !empty($validated['is_piutang']) || $paidAmount < $total;

                    // lockForUpdate di sini bikin transaksi lain yang lagi generate invoice
                    // di hari yang sama antre, bukan cuma count biasa.
                    $todayCount = Transaction::whereDate('created_at', today())->lockForUpdate()->count();

                    $transaction = Transaction::create([
                        'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT),
                        'user_id' => auth()->id(),
                        'customer_id' => $validated['customer_id'] ?? null,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'tax' => $tax,
                        'additional_fee' => $additionalFee,
                        'total' => $total,
                        'payment_method' => $validated['payment_method'],
                        'status' => $isPiutang ? 'piutang' : 'lunas',
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

                            // Cuma potong stok produk kalau memang dia yang melacak stoknya sendiri.
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

                            // Kalau produk ini punya resep bahan baku (Es Teh Susu -> Susu),
                            // potong stok bahan sesuai qty_used * qty terjual, apapun status tracks_stock-nya.
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
                            'note' => 'Pembayaran awal',
                        ]);
                    }

                    return $transaction;
                });

                return redirect()->route('transactions.show', $transaction)->with('success', 'Transaksi berhasil disimpan.');

            } catch (QueryException $e) {
                // Kemungkinan invoice_number bentrok karena dua transaksi dibuat nyaris bersamaan.
                // Syaratnya kolom invoice_number harus unique di migration supaya retry ini kepakai.
                $isDuplicateInvoice = str_contains(strtolower($e->getMessage()), 'invoice_number');
                if (!$isDuplicateInvoice || $attempts >= 3) {
                    throw $e;
                }
                // lanjut loop, invoice_number akan digenerate ulang di percobaan berikutnya
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        } while ($attempts < 3);

        return back()->withInput()->with('error', 'Transaksi gagal disimpan, silakan coba lagi.');
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'items.variant', 'user', 'customer', 'payments' => fn ($q) => $q->oldest()]);
        return view('transactions.show', compact('transaction'));
    }

    public function payPiutang(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'piutang') {
            return back()->with('error', 'Transaksi ini bukan piutang atau sudah lunas.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
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
                'note' => $validated['note'] ?? null,
            ]);

            if ($validated['amount'] >= $sisa) {
                $transaction->update(['status' => 'lunas']);
            }
        });

        return back()->with('success', 'Pembayaran piutang berhasil dicatat.');
    }

    /**
     * Batalkan transaksi. Stok produk/varian & bahan baku yang tadi dipotong
     * pas store() dikembalikan lagi di sini, dicatat sebagai StockMovement/
     * IngredientStockMovement tipe 'in' biar tetap ketelusur di riwayat stok.
     */
    public function cancel(Request $request, Transaction $transaction)
    {
        if ($transaction->status === 'batal') {
            return back()->with('error', 'Transaksi ini sudah dibatalkan sebelumnya.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($transaction, $validated) {
            $transaction->load('items.product.ingredients', 'items.variant');

            $note = 'Pembatalan transaksi ' . $transaction->invoice_number
                . (!empty($validated['reason']) ? ' - ' . $validated['reason'] : '');

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
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaksi berhasil dibatalkan, stok sudah dikembalikan.');
    }

    /**
     * Download struk transaksi sebagai PDF (butuh package barryvdh/laravel-dompdf).
     */
    public function downloadPdf(Transaction $transaction)
    {
        $transaction->load(['items.product', 'items.variant', 'user', 'customer', 'payments' => fn ($q) => $q->oldest()]);
        $tenant = auth()->user()->tenant ?? null;

        $pdf = Pdf::loadView('transactions.pdf', compact('transaction', 'tenant'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('struk-' . $transaction->invoice_number . '.pdf');
    }
}