<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Exports\KeuanganExport;
use App\Exports\StokExport;
use App\Exports\PiutangExport;
use App\Services\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $this->resolvePeriodKey($request);
        [$start, $end] = $this->resolveRange($period, $request);

        $summary = $this->buildSummary($start, $end);
        $dailyRecap = $this->buildDailyRecap($start, $end);
        $productRecap = $this->buildProductRecap($start, $end);
        $piutangRecap = $this->buildPiutangRecap($start, $end);

        $paymentRecap = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'lunas')
            ->select('payment_method')
            ->selectRaw('COUNT(*) as jumlah, SUM(total) as omzet')
            ->groupBy('payment_method')
            ->get();

        $statusRecap = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'batal')
            ->select('status')
            ->selectRaw('COUNT(*) as jumlah, SUM(total) as nilai')
            ->groupBy('status')
            ->get();

        return view('reports.index', compact(
            'period', 'start', 'end', 'summary', 'dailyRecap', 'productRecap',
            'paymentRecap', 'statusRecap', 'piutangRecap'
        ));
    }

    public function exportPdf(Request $request, string $type)
    {
        $period = $this->resolvePeriodKey($request);
        [$start, $end] = $this->resolveRange($period, $request);

        $data = $this->dataFor($type, $start, $end);

        $pdf = Pdf::loadView("reports.pdf.{$type}", $data)->setPaper('a4', 'landscape');
        return $pdf->download("laporan-{$type}-" . now()->format('Ymd_His') . '.pdf');
    }

    public function exportExcel(Request $request, string $type)
    {
        $period = $this->resolvePeriodKey($request);
        [$start, $end] = $this->resolveRange($period, $request);

        $export = match ($type) {
            'keuangan' => new KeuanganExport($start, $end),
            'stok'     => new StokExport(),
            'piutang'  => new PiutangExport($start, $end),
            default    => abort(404),
        };

        return Excel::download($export, "laporan-{$type}-" . now()->format('Ymd_His') . '.xlsx');
    }

    private function dataFor(string $type, Carbon $start, Carbon $end): array
    {
        return match ($type) {
            'keuangan' => [
                'start' => $start, 'end' => $end,
                'summary' => $this->buildSummary($start, $end),
                'dailyRecap' => $this->buildDailyRecap($start, $end),
                'productRecap' => $this->buildProductRecap($start, $end),
                'piutangRecap' => $this->buildPiutangRecap($start, $end),
            ],
            'stok' => [
                'stockRecap' => $this->buildStockRecap(),
            ],
            'piutang' => [
                'start' => $start, 'end' => $end,
                'piutangRecap' => $this->buildPiutangRecap($start, $end),
            ],
            default => abort(404),
        };
    }

    private function resolvePeriodKey(Request $request): string
    {
        return in_array($request->get('period'), ['today', 'week', 'month', 'custom'])
            ? $request->get('period')
            : 'month';
    }

    private function resolveRange(string $period, Request $request): array
    {
        if ($period === 'today') {
            return [today()->startOfDay(), today()->endOfDay()];
        }

        if ($period === 'week') {
            return [now()->startOfWeek(), now()->endOfWeek()];
        }

        if ($period === 'custom') {
            $start = $request->filled('start') && strtotime($request->get('start'))
                ? Carbon::parse($request->get('start'))->startOfDay()
                : now()->startOfMonth();

            $end = $request->filled('end') && strtotime($request->get('end'))
                ? Carbon::parse($request->get('end'))->endOfDay()
                : now()->endOfDay();

            if ($start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            if ($start->diffInDays($end) > 366) {
                $end = $start->copy()->addDays(366)->endOfDay();
            }

            return [$start, $end];
        }

        return [now()->startOfMonth(), now()->endOfMonth()];
    }

    /**
     * Ringkasan utama laporan. PRINSIP: omzet & profit cuma dari transaksi LUNAS.
     * Transaksi PIUTANG ditampilkan terpisah (belum diakui sebagai omzet).
     */
    private function buildSummary(Carbon $start, Carbon $end): array
    {
        $lunasQuery = Transaction::whereBetween('created_at', [$start, $end])->where('status', 'lunas');
        $piutangQuery = Transaction::whereBetween('created_at', [$start, $end])->where('status', 'piutang');

        $summary = [
            'jumlah_transaksi' => (clone $lunasQuery)->count() + (clone $piutangQuery)->count(),
            'jumlah_lunas' => (clone $lunasQuery)->count(),
            'jumlah_piutang' => (clone $piutangQuery)->count(),

            'omzet' => (float) (clone $lunasQuery)->sum('total'),
            'subtotal' => (float) (clone $lunasQuery)->sum('subtotal'),
            'diskon' => (float) (clone $lunasQuery)->sum('discount'),
            'pajak' => (float) (clone $lunasQuery)->sum('tax'),
            'biaya_tambahan' => (float) (clone $lunasQuery)->sum('additional_fee'),
        ];

        $summary['modal'] = $this->totalModal($start, $end, 'lunas');
        $summary['profit'] = $summary['omzet'] - $summary['modal'];
        $summary['margin'] = $summary['omzet'] > 0 ? round(($summary['profit'] / $summary['omzet']) * 100, 1) : 0;
        $summary['rata_rata_transaksi'] = $summary['jumlah_lunas'] > 0
            ? $summary['omzet'] / $summary['jumlah_lunas']
            : 0;

        $piutangTransaksi = (clone $piutangQuery)->withSum('payments', 'amount')->get();
        $summary['piutang_nilai'] = (float) $piutangTransaksi->sum('total');
        $summary['piutang_sudah_dibayar'] = (float) $piutangTransaksi->sum(fn ($t) => $t->payments_sum_amount ?? 0);
        $summary['piutang_sisa'] = $summary['piutang_nilai'] - $summary['piutang_sudah_dibayar'];

        $summary['kas_masuk'] = (float) Payment::whereDate('paid_at', '>=', $start->format('Y-m-d'))
            ->whereDate('paid_at', '<=', $end->format('Y-m-d'))
            ->sum('amount');

        return $summary;
    }

    private function totalModal(Carbon $start, Carbon $end, string $status = 'lunas'): float
    {
        return (float) TransactionItem::whereHas('transaction', function ($q) use ($start, $end, $status) {
                $q->whereBetween('created_at', [$start, $end])->where('status', $status);
            })
            ->with(['product:id,price_modal', 'variant:id,price_modal'])
            ->get()
            ->sum(fn ($item) => ($item->variant->price_modal ?? $item->product->price_modal ?? 0) * $item->qty);
    }

    private function buildDailyRecap(Carbon $start, Carbon $end)
    {
        $dailyLunas = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'lunas')
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah_transaksi, SUM(total) as omzet')
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $dailyPiutang = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'piutang')
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah_transaksi, SUM(total) as nilai')
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        // FIX: query ini pakai DB::table() langsung (raw query builder), jadi gak
        // pernah kena global scope tenant dari model Transaction/TransactionItem.
        // Tanpa filter manual, modal harian ini ke-hitung dari SEMUA tenant.
        $tenantContext = app(TenantContext::class);
        $isGlobal = !$tenantContext->check(); // superadmin tanpa impersonate = lihat semua

        $dailyModal = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->leftJoin('products', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'transaction_items.product_variant_id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'lunas')
            ->when(!$isGlobal, fn ($q) => $q->where('transactions.tenant_id', $tenantContext->id()))
            ->selectRaw('DATE(transactions.created_at) as tanggal, SUM(transaction_items.qty * COALESCE(product_variants.price_modal, products.price_modal, 0)) as modal')
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $recap = collect();
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $lunas = $dailyLunas->get($key);
            $piutang = $dailyPiutang->get($key);
            $modal = (float) ($dailyModal->get($key)->modal ?? 0);
            $omzet = (float) ($lunas->omzet ?? 0);

            $recap->push([
                'tanggal' => $cursor->copy(),
                'jumlah_transaksi' => ($lunas->jumlah_transaksi ?? 0) + ($piutang->jumlah_transaksi ?? 0),
                'jumlah_lunas' => $lunas->jumlah_transaksi ?? 0,
                'jumlah_piutang' => $piutang->jumlah_transaksi ?? 0,
                'omzet' => $omzet,
                'modal' => $modal,
                'profit' => $omzet - $modal,
                'margin' => $omzet > 0 ? round((($omzet - $modal) / $omzet) * 100, 1) : 0,
                'piutang_baru' => (float) ($piutang->nilai ?? 0),
            ]);

            $cursor->addDay();
        }

        return $recap;
    }

    private function buildProductRecap(Carbon $start, Carbon $end)
    {
        return TransactionItem::select('product_id', 'product_variant_id')
            ->selectRaw('SUM(qty) as total_qty')
            ->selectRaw('SUM(subtotal) as total_omzet')
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])->where('status', 'lunas');
            })
            ->groupBy('product_id', 'product_variant_id')
            ->with(['product:id,name,price_modal', 'variant:id,name,price_modal'])
            ->orderByDesc('total_omzet')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                $modalSatuan = $item->variant->price_modal ?? $item->product->price_modal ?? 0;
                $modal = $modalSatuan * $item->total_qty;
                $omzet = (float) $item->total_omzet;

                $name = $item->product->name ?? 'Produk tidak ditemukan';
                if ($item->variant) {
                    $name .= ' - ' . $item->variant->name;
                }

                return [
                    'name' => $name,
                    'qty' => (int) $item->total_qty,
                    'omzet' => $omzet,
                    'modal' => (float) $modal,
                    'profit' => $omzet - $modal,
                    'margin' => $omzet > 0 ? round((($omzet - $modal) / $omzet) * 100, 1) : 0,
                ];
            });
    }

    private function buildStockRecap()
    {
        return Product::select('id', 'name', 'stock', 'min_stock', 'price_modal', 'price_jual')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'name' => $p->name,
                'stock' => $p->stock,
                'min_stock' => $p->min_stock,
                'status' => $p->stock <= $p->min_stock ? 'Menipis' : 'Aman',
                'nilai_modal' => $p->stock * $p->price_modal,
                'nilai_jual' => $p->stock * ($p->price_jual ?? 0),
            ]);
    }

    private function buildPiutangRecap(Carbon $start, Carbon $end)
    {
        return Transaction::where('status', 'piutang')
            ->whereBetween('created_at', [$start, $end])
            ->with('customer')
            ->withSum('payments', 'amount')
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'tanggal' => $t->created_at,
                'invoice' => $t->invoice_number ?? $t->id,
                'customer' => $t->customer->name ?? 'Pelanggan Umum',
                'total' => $t->total,
                'dibayar' => $t->payments_sum_amount ?? 0,
                'sisa' => $t->total - ($t->payments_sum_amount ?? 0),
            ]);
    }
}