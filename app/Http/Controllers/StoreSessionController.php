<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\StoreSession;
use App\Services\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreSessionController extends Controller
{
    /**
     * Buka toko: catat uang laci awal dan mulai sesi baru.
     */
    public function open(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return back()->with('error', 'Tidak ada tenant aktif, tidak bisa membuka toko.');
        }

        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        try {
            StoreSession::create([
                'tenant_id' => $tenantId,
                'opened_by' => auth()->id(),
                'opened_at' => now(),
                'business_date' => today()->toDateString(),
                'opening_cash' => $validated['opening_cash'],
                'open_marker' => 1,
            ]);
        } catch (QueryException $e) {
            // Unique (tenant_id, open_marker): berarti sudah ada sesi yang masih terbuka.
            if ($e->getCode() === '23000') {
                return redirect()->route('transactions.create')
                    ->with('error', 'Toko sudah dalam keadaan buka.');
            }

            throw $e;
        }

        return redirect()->route('transactions.create')->with('success', 'Toko dibuka. Selamat berjualan!');
    }

    /**
     * Tutup toko: hitung kas yang seharusnya ada di laci, bandingkan dengan
     * hasil hitung fisik, simpan selisihnya.
     */
    public function close(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return back()->with('error', 'Tidak ada tenant aktif.');
        }

        $validated = $request->validate([
            'closing_cash_counted' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $result = DB::transaction(function () use ($tenantId, $validated) {
                $session = StoreSession::where('tenant_id', $tenantId)
                    ->whereNull('closed_at')
                    ->lockForUpdate()
                    ->first();

                if (!$session) {
                    throw new \RuntimeException('Toko sudah tidak dalam keadaan buka.');
                }

                $closedAt = now();
                $summary = $session->summary($closedAt);

                $counted = (float) $validated['closing_cash_counted'];
                $difference = $counted - $summary['expected_cash'];

                $session->update([
                    'closed_by' => auth()->id(),
                    'closed_at' => $closedAt,
                    'expected_cash' => $summary['expected_cash'],
                    'closing_cash_counted' => $counted,
                    'cash_difference' => $difference,
                    'note' => $validated['note'] ?? null,
                    'open_marker' => null,
                ]);

                return [
                    'expected' => $summary['expected_cash'],
                    'counted' => $counted,
                    'difference' => $difference,
                ];
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('transactions.index')->with('error', $e->getMessage());
        }

        $fmt = fn ($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');

        if (abs($result['difference']) < 0.5) {
            $selisih = 'kas pas, tidak ada selisih.';
        } elseif ($result['difference'] > 0) {
            $selisih = 'kas LEBIH ' . $fmt($result['difference']) . '.';
        } else {
            $selisih = 'kas KURANG ' . $fmt($result['difference']) . '.';
        }

        return redirect()->route('transactions.index')->with(
            'success',
            'Toko ditutup. Kas seharusnya ' . $fmt($result['expected'])
            . ', hitungan fisik ' . $fmt($result['counted']) . ' — ' . $selisih
        );
    }

    /**
     * Catat pengeluaran dari halaman kasir. Otomatis masuk ke sesi yang
     * sedang buka (tanggal pengeluaran = tanggal bisnis sesi, bukan tanggal
     * kalender, jadi pengeluaran jam 00:30 tetap kebawa ke hari buka toko).
     */
    public function storeExpense(Request $request)
    {
        $session = StoreSession::current();

        if (!$session) {
            return back()->with('error', 'Toko belum dibuka, pengeluaran belum bisa dicatat dari kasir.');
        }

        $validated = $request->validate([
            'expense_type_id' => [
                'required',
                Rule::exists('expense_types', 'id')->where('is_active', true),
            ],
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        Expense::create([
            'expense_type_id' => $validated['expense_type_id'],
            'amount' => $validated['amount'],
            'expense_date' => $session->business_date->toDateString(),
            'note' => $validated['note'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Pengeluaran berhasil dicatat dan mengurangi kas laci.');
    }
}