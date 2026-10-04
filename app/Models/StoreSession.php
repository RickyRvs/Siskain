<?php

namespace App\Models;

use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StoreSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'business_date' => 'date',
        'opening_cash' => 'float',
        'expected_cash' => 'float',
        'closing_cash_counted' => 'float',
        'cash_difference' => 'float',
    ];

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Sesi yang sedang buka untuk tenant aktif. Null kalau toko lagi tutup
     * (atau gak ada tenant aktif, mis. superadmin yang belum impersonate).
     */
    public static function current(): ?self
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return null;
        }

        return static::where('tenant_id', $tenantId)
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();
    }

    /**
     * Sesi terakhir (buka atau sudah tutup) untuk tenant aktif.
     * Dipakai buat nampilin angka "terakhir" waktu toko lagi tutup.
     */
    public static function latestForTenant(): ?self
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return null;
        }

        return static::where('tenant_id', $tenantId)
            ->latest('opened_at')
            ->first();
    }

    /**
     * Ringkasan kas & penjualan selama sesi ini.
     *
     * Semua angka dihitung dari jendela waktu sesi (opened_at s/d closed_at,
     * atau sampai sekarang kalau masih buka), jadi tabel transactions, payments,
     * dan expenses gak perlu kolom tambahan. Query Transaction/Payment/Expense
     * otomatis kena scope tenant yang sama seperti di controller lain.
     *
     * Kas laci HANYA menghitung pembayaran tunai. Transfer & QRIS gak masuk laci.
     * Semua pengeluaran yang dicatat selama sesi dianggap keluar dari laci.
     */
    public function summary(?Carbon $until = null): array
    {
        $from = $this->opened_at;
        $to = $until ?? $this->closed_at ?? now();

        // Pembayaran dari transaksi yang dibatalkan gak dihitung (sama kayak laporan Kas Masuk).
        $cashIn = (float) Payment::where('payment_method', 'tunai')
            ->whereBetween('created_at', [$from, $to])
            ->whereHas('transaction', fn ($q) => $q->where('status', '!=', 'batal'))
            ->sum('amount');

        $expenses = (float) Expense::whereBetween('created_at', [$from, $to])->sum('amount');

        $transactions = Transaction::whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'batal');

        $lunas = (clone $transactions)->where('status', 'lunas');
        $piutang = (clone $transactions)->where('status', 'piutang');

        return [
            'opening_cash' => (float) $this->opening_cash,
            'cash_in' => $cashIn,
            'expenses' => $expenses,
            'expected_cash' => (float) $this->opening_cash + $cashIn - $expenses,

            'transaction_count' => (clone $transactions)->count(),
            'omzet' => (float) (clone $lunas)->sum('total'),
            'piutang_count' => (clone $piutang)->count(),
            'piutang_baru' => (float) (clone $piutang)->sum('total'),
        ];
    }
}