<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }

    public function piutang()
    {
        $customers = Customer::whereHas('transactions', fn ($q) => $q->where('status', 'piutang'))
            ->with(['transactions' => fn ($q) => $q->where('status', 'piutang')->with('payments')->oldest()])
            ->get();

        return view('customers.piutang', compact('customers'));
    }

    /**
     * Bayar piutang customer sekaligus (bukan per transaksi).
     * Jumlah yang dibayar dialokasikan otomatis ke invoice yang paling lama dulu (FIFO),
     * sisanya lanjut ke invoice berikutnya sampai jumlah bayar habis.
     *
     * NOTE: method ini mengasumsikan Transaction punya relasi payments() (hasMany Payment)
     * dan Payment punya kolom `amount` yang fillable. Kalau skema Payment kamu beda
     * (misal butuh user_id, note, dsb yang required), sesuaikan array create() di bawah.
     */
    public function payPiutang(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $transactions = $customer->transactions()
            ->where('status', 'piutang')
            ->oldest()
            ->get()
            ->filter(fn ($t) => $t->sisaPiutang() > 0)
            ->values();

        $totalSisa = $transactions->sum(fn ($t) => $t->sisaPiutang());

        if ($transactions->isEmpty() || $totalSisa <= 0) {
            return back()->with('error', 'Customer ini tidak punya piutang aktif.');
        }

        if ($validated['amount'] > $totalSisa) {
            return back()->withErrors(['amount' => 'Jumlah bayar melebihi total piutang customer ini (Rp '.number_format($totalSisa, 0, ',', '.').').'])->withInput();
        }

        $remaining = $validated['amount'];
        $invoicesTouched = 0;

        foreach ($transactions as $transaction) {
            if ($remaining <= 0) {
                break;
            }

            $sisa = $transaction->sisaPiutang();
            $pay = min($remaining, $sisa);

            $transaction->payments()->create([
                'amount' => $pay,
            ]);

            if ($pay >= $sisa) {
                $transaction->update(['status' => 'lunas']);
            }

            $remaining -= $pay;
            $invoicesTouched++;
        }

        return redirect()->route('customers.piutang')->with(
            'success',
            'Pembayaran Rp '.number_format($validated['amount'], 0, ',', '.')." berhasil dialokasikan ke {$invoicesTouched} invoice (dari yang terlama)."
        );
    }
}