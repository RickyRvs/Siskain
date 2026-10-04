<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ExpenseType;
use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StoreSession;
use App\Models\Transaction;
use App\Services\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

        // Kartu "hari ini" sekarang dihitung per SESI TOKO, bukan per tanggal kalender,
        // jadi toko yang buka sampai lewat tengah malam angkanya gak kepotong.
        // Kalau toko lagi tutup, yang ditampilkan sesi terakhir. Kalau belum pernah
        // ada sesi sama sekali, jatuh balik ke tanggal hari ini.
        $window = StoreSession::current() ?? StoreSession::latestForTenant();

        if ($window) {
            $from = $window->opened_at;
            $to = $window->closed_at ?? now();
        } else {
            $from = today()->startOfDay();
            $to = today()->endOfDay();
        }

        $todayQuery = Transaction::whereBetween('created_at', [$from, $to])->where('status', '!=', 'batal');

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
        $storeSession = StoreSession::current();

        // Toko belum dibuka: halaman kasir cuma nampilin form "Buka Toko",
        // jadi gak perlu narik katalog produk, customer, dan jenis pengeluaran.
        $sessionSummary = $storeSession ? $storeSession->summary() : null;
        $products = $storeSession ? $this->catalogProducts() : collect();
        $customers = $storeSession ? Customer::orderBy('name')->get() : collect();
        $expenseTypes = $storeSession ? ExpenseType::where('is_active', true)->orderBy('name')->get() : collect();

        return view('transactions.create', compact(
            'products', 'customers', 'storeSession', 'sessionSummary', 'expenseTypes'
        ));
    }

    /**
     * Query produk aktif buat katalog kasir. Dipakai bareng-bareng
     * sama create() dan show() (pas mode "tambah item" ke invoice lama),
     * biar daftar produknya konsisten di kedua tempat.
     *
     * ->active() : produk yang dinonaktifkan owner gak boleh muncul lagi
     * di katalog kasir, walaupun stoknya masih ada.
     */
    private function catalogProducts()
    {
        return Product::with(['variants', 'ingredients', 'category'])
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

        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return back()->withInput()->with('error', 'Tidak ada tenant aktif, tidak bisa membuat transaksi.');
        }

        // Transaksi cuma boleh dibuat selama toko dalam keadaan buka, biar semua
        // penjualan dan uang masuknya kecatat di sesi yang benar.
        if (!StoreSession::current()) {
            return back()->withInput()->with('error', 'Toko belum dibuka. Buka toko dulu sebelum membuat transaksi.');
        }

        $attempts = 0;
        $maxAttempts = 5;

        do {
            $attempts++;

            // Tanggal diambil SEKALI per percobaan, lalu dipakai untuk key counter DAN
            // untuk string nomor invoice. Sebelumnya string invoice pakai now() terpisah,
            // jadi kalau request kebetulan melintasi jam 00:00 nomor yang dihasilkan bisa
            // pakai tanggal baru tapi urutannya dari counter tanggal lama.
            $today = today()->toDateString();

            // Generate invoice number DI LUAR DB::transaction() di bawah. Kalau digenerate
            // di dalam, rollback karena duplicate invoice_number ikut me-rollback increment
            // counter, jadi retry dapat nomor yang sama terus. Di luar transaksi, incrementnya
            // permanen walau transaksi utama gagal.
            $invoiceNumber = $this->generateInvoiceNumber($tenantId, $today);

            try {
                $transaction = DB::transaction(function () use ($validated, $invoiceNumber) {
                    $subtotal = 0;
                    $itemsData = [];

                    foreach ($validated['items'] as $item) {
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

                    // Status ditentukan MURNI dari nominal yang dibayar vs total, bukan dari
                    // checkbox "is_piutang" semata.
                    $status = $paidAmount >= $total ? 'lunas' : 'piutang';

                    $transaction = Transaction::create([
                        'invoice_number' => $invoiceNumber,
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
                if (!$isDuplicateInvoice || $attempts >= $maxAttempts) {
                    throw $e;
                }

                // Self-heal: ini kejadian tandanya invoice_counters ketinggalan
                // dibanding data yang SUDAH ADA di tabel transactions (mis. hasil
                // seed/import manual yang gak lewat generateInvoiceNumber()).
                // Samakan counter ke nomor terbesar yang beneran kepakai hari ini,
                // biar percobaan berikutnya gak collide ke nomor yang sama lagi.
                $this->resyncInvoiceCounter($tenantId, $today);

            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        } while ($attempts < $maxAttempts);

        return back()->withInput()->with('error', 'Transaksi gagal disimpan, silakan coba lagi.');
    }

    /**
     * Generate invoice number secara atomic pakai tabel counter terpisah per tenant.
     * Format: INV-{tenant_id}-{Ymd}-{4 digit urut}. tenant_id dimasukkan ke
     * string-nya sendiri supaya nggak pernah collide dengan nomor lama
     * (format lama tanpa tenant_id) maupun dengan tenant lain, walau
     * angka urutnya kebetulan sama.
     *
     * Tanggal di nomor invoice tetap tanggal kalender (bukan tanggal sesi),
     * jadi invoice jam 00:30 pakai tanggal hari berikutnya. Itu wajar dan
     * gak ngaruh ke pengelompokan sesi, karena sesi dihitung dari jam transaksi.
     */
    private function generateInvoiceNumber(int $tenantId, string $today): string
    {
        DB::statement(
            'INSERT INTO invoice_counters (tenant_id, date, last_number, created_at, updated_at)
             VALUES (?, ?, LAST_INSERT_ID(1), NOW(), NOW())
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)',
            [$tenantId, $today]
        );

        $number = (int) DB::getPdo()->lastInsertId();

        return 'INV-' . $tenantId . '-' . Carbon::parse($today)->format('Ymd') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Selaraskan invoice_counters dengan data transaksi yang SUDAH BENERAN
     * ada di tabel transactions untuk tenant & tanggal ini. Dipanggil otomatis
     * waktu insert transaksi kena duplicate invoice_number — biasanya artinya
     * counter ketinggalan dibanding data (mis. hasil seed/import manual yang
     * gak lewat generateInvoiceNumber() di atas).
     *
     * GREATEST() dipakai supaya ini aman dipanggil kapan pun: kalau counter
     * ternyata udah lebih tinggi dari data (kasus normal), nilainya gak
     * diturunkan/dirusak - cuma dinaikkan kalau memang ketinggalan.
     */
    private function resyncInvoiceCounter(int $tenantId, string $today): void
    {
        $prefix = 'INV-' . $tenantId . '-' . Carbon::parse($today)->format('Ymd') . '-';

        $maxUsed = (int) (DB::table('transactions')
            ->where('tenant_id', $tenantId)
            ->where('invoice_number', 'like', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(invoice_number, ?) AS UNSIGNED)) as max_used', [strlen($prefix) + 1])
            ->value('max_used') ?? 0);

        DB::statement(
            'INSERT INTO invoice_counters (tenant_id, date, last_number, created_at, updated_at)
             VALUES (?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE last_number = GREATEST(last_number, ?)',
            [$tenantId, $today, $maxUsed, $maxUsed]
        );
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'items.variant', 'user', 'customer', 'payments' => fn ($q) => $q->oldest()]);

        // "Tambah Item" cuma dibolehin kalau transaksinya belum dibatalkan, toko
        // masih buka, dan transaksinya dibuat di sesi toko yang sedang berjalan.
        // Di luar itu, ngedit invoice dari sesi yang sudah ditutup beresiko bikin
        // rekap kas sesi tersebut gak nyambung lagi sama fisik uangnya.
        $storeSession = StoreSession::current();

        $canAddItems = $transaction->status !== 'batal'
            && $storeSession !== null
            && $transaction->created_at->gte($storeSession->opened_at);

        $products = $canAddItems ? $this->catalogProducts() : collect();

        return view('transactions.show', compact('transaction', 'canAddItems', 'products'));
    }

    /**
     * Tambah item ke transaksi yang sudah ada (dipakai buat kasus customer
     * pesan lagi setelah invoice pertama sudah lunas, di sesi toko yang sama).
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

                $storeSession = StoreSession::current();

                if (!$storeSession) {
                    throw new \RuntimeException('Toko belum dibuka, tidak bisa menambah item.');
                }

                if ($transaction->created_at->lt($storeSession->opened_at)) {
                    throw new \RuntimeException('Item cuma bisa ditambahkan ke transaksi dari sesi toko yang sedang berjalan.');
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

                // Sama kayak store(): status murni dari nominal terbayar vs total,
                // checkbox is_piutang gak lagi bisa maksa status piutang kalau
                // nominalnya sebenarnya udah cukup/lebih.
                $status = $newPaidAmount >= $newTotal ? 'lunas' : 'piutang';

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

        // Pembayaran piutang itu uang masuk. Biar kecatat di kas laci sesi yang
        // benar, toko harus lagi buka pas pembayarannya dicatat.
        if (!StoreSession::current()) {
            return back()->with('error', 'Toko belum dibuka. Buka toko dulu supaya pembayaran masuk ke kas laci.');
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

    /**
     * Perbaikan sekali-jalan buat transaksi lama yang statusnya masih "piutang"
     * padahal paid_amount udah >= total (harusnya lunas). Bug lamanya ada di
     * store()/addItems() yang sempat maksa status jadi piutang cuma dari
     * checkbox is_piutang, udah dipatch — command ini buat benerin data yang
     * kadung tersimpan salah SEBELUM patch itu ada. Nyisir SEMUA tenant
     * sekaligus (withoutGlobalScopes) biar sekali klik langsung beres semua.
     * Aman dijalankan berkali-kali — kalau udah gak ada yang salah, cuma
     * ngasih notif "gak ada yang perlu diperbaiki".
     */
    public function fixPiutangStatus()
    {
        $candidates = Transaction::withoutGlobalScopes()
            ->where('status', 'piutang')
            ->whereColumn('paid_amount', '>=', 'total')
            ->get();

        if ($candidates->isEmpty()) {
            return back()->with('success', 'Gak ada transaksi yang perlu diperbaiki, semua status sudah benar.');
        }

        DB::transaction(function () use ($candidates) {
            foreach ($candidates as $t) {
                $t->update(['status' => 'lunas']);
            }
        });

        $list = $candidates->pluck('invoice_number')->implode(', ');

        return back()->with('success', "{$candidates->count()} transaksi diperbaiki jadi lunas: {$list}");
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