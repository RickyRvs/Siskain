<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $yesterday = today()->subDay();

        // --- Transaksi hari ini, dipisah per status (exclude batal) ---
        $todayLunasQuery = Transaction::whereDate('created_at', $today)->where('status', 'lunas');
        $todayPiutangQuery = Transaction::whereDate('created_at', $today)->where('status', 'piutang');

        $todayLunasCount = (clone $todayLunasQuery)->count();
        $todayPiutangCount = (clone $todayPiutangQuery)->count();
        $todayTransactionCount = $todayLunasCount + $todayPiutangCount;

        // Omzet HANYA dari transaksi lunas, biar konsisten sama halaman Laporan.
        $todayRevenue = (float) (clone $todayLunasQuery)->sum('total');
        $todayPiutangBaru = (float) (clone $todayPiutangQuery)->sum('total');

        $yesterdayRevenue = (float) Transaction::whereDate('created_at', $yesterday)
            ->where('status', 'lunas')
            ->sum('total');

        $revenueGrowth = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : ($todayRevenue > 0 ? 100 : 0);

        // --- Laba kotor hari ini: cuma dari item transaksi yang LUNAS ---
        $todayGrossProfit = TransactionItem::whereHas('transaction', function ($q) use ($today) {
                $q->whereDate('created_at', $today)->where('status', 'lunas');
            })
            ->with(['product:id,price_modal', 'variant:id,price_modal'])
            ->get()
            ->sum(function ($item) {
                $modal = $item->variant->price_modal ?? $item->product->price_modal ?? 0;
                return $item->subtotal - ($modal * $item->qty);
            });

        $todayMargin = $todayRevenue > 0 ? round(($todayGrossProfit / $todayRevenue) * 100, 1) : 0;

        // --- Kas masuk hari ini: berdasarkan tanggal BAYAR (paid_at), termasuk
        // cicilan piutang lama yang baru dilunasi/dibayar hari ini. Cash flow riil,
        // beda dari 'todayRevenue' yang basisnya akrual (tanggal transaksi lunas). ---
        $todayKasMasuk = (float) Payment::whereDate('paid_at', $today)->sum('amount');

        // --- Stok ---
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
            ->orderByRaw('stock - min_stock asc')
            ->limit(5)
            ->get(['id', 'name', 'stock', 'min_stock']);

        // --- Piutang aktif (total keseluruhan, bukan cuma hari ini) ---
        $piutangTransactions = Transaction::where('status', 'piutang')
            ->with('customer')
            ->withSum('payments', 'amount')
            ->get();

        $piutangTotal = $piutangTransactions->sum(fn ($t) => $t->total - ($t->payments_sum_amount ?? 0));
        $piutangCount = $piutangTransactions->count();

        $topDebtors = $piutangTransactions
            ->groupBy('customer_id')
            ->map(function ($group) {
                $customer = $group->first()->customer;
                return [
                    'name' => $customer ? $customer->name : 'Pelanggan Umum',
                    'sisa' => $group->sum(fn ($t) => $t->total - ($t->payments_sum_amount ?? 0)),
                    'jumlah_invoice' => $group->count(),
                ];
            })
            ->sortByDesc('sisa')
            ->take(5)
            ->values();

        // --- Grafik penjualan 7 hari terakhir: omzet lunas + piutang baru per hari ---
        $salesTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);

            $omzet = (float) Transaction::whereDate('created_at', $date)
                ->where('status', 'lunas')
                ->sum('total');

            $piutangBaru = (float) Transaction::whereDate('created_at', $date)
                ->where('status', 'piutang')
                ->sum('total');

            return [
                'label' => $date->translatedFormat('D'),
                'tanggal' => $date->translatedFormat('d M'),
                'omzet' => $omzet,
                'piutang_baru' => $piutangBaru,
            ];
        });

        $weekOmzet = $salesTrend->sum('omzet');
        $weekPiutangBaru = $salesTrend->sum('piutang_baru');

        // --- Produk terlaris 30 hari terakhir, cuma dari transaksi LUNAS ---
        $topProducts = TransactionItem::select('product_id', 'product_variant_id')
            ->selectRaw('SUM(qty) as total_qty')
            ->selectRaw('SUM(subtotal) as total_revenue')
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'lunas')
                    ->where('created_at', '>=', now()->subDays(30));
            })
            ->groupBy('product_id', 'product_variant_id')
            ->with(['product:id,name', 'variant:id,name'])
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $name = $item->product->name ?? 'Produk tidak ditemukan';
                if ($item->variant) {
                    $name .= ' - ' . $item->variant->name;
                }
                return [
                    'name' => $name,
                    'qty' => (int) $item->total_qty,
                    'revenue' => (float) $item->total_revenue,
                ];
            });

        // --- Breakdown metode pembayaran hari ini, cuma dari transaksi LUNAS ---
        $paymentBreakdown = (clone $todayLunasQuery)
            ->select('payment_method')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('payment_method')
            ->pluck('jumlah', 'payment_method');

        // --- Aktivitas terbaru (semua status, biar tetap kelihatan mana yang piutang/batal) ---
        $recentTransactions = Transaction::with('customer')
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'todayTransactionCount',
            'todayLunasCount',
            'todayPiutangCount',
            'todayRevenue',
            'todayPiutangBaru',
            'revenueGrowth',
            'todayGrossProfit',
            'todayMargin',
            'todayKasMasuk',
            'lowStockCount',
            'lowStockProducts',
            'piutangTotal',
            'piutangCount',
            'topDebtors',
            'salesTrend',
            'weekOmzet',
            'weekPiutangBaru',
            'topProducts',
            'paymentBreakdown',
            'recentTransactions'
        ));
    }
}