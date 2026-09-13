<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_amount' => 'nullable|numeric|min:0',
        ]);

        ExpenseType::create($validated);

        return back()->with('success', 'Jenis pengeluaran berhasil ditambahkan.');
    }

    public function update(Request $request, ExpenseType $expenseType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_amount' => 'nullable|numeric|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $expenseType->update($validated);

        return back()->with('success', 'Jenis pengeluaran berhasil diperbarui.');
    }

    public function destroy(ExpenseType $expenseType)
    {
        if ($expenseType->expenses()->exists()) {
            return back()->with('error', 'Jenis pengeluaran ini masih dipakai di riwayat pengeluaran, gak bisa dihapus. Nonaktifkan aja biar gak muncul lagi di pilihan.');
        }

        $expenseType->delete();

        return back()->with('success', 'Jenis pengeluaran berhasil dihapus.');
    }
}