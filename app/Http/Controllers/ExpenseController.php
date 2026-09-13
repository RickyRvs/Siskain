<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with('expenseType')
            ->when($request->filled('expense_type_id'), fn ($q) =>
                $q->where('expense_type_id', $request->expense_type_id))
            ->when($request->filled('date_from'), fn ($q) =>
                $q->whereDate('expense_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) =>
                $q->whereDate('expense_date', '<=', $request->date_to))
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        $totalBulanIni = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // Yang aktif aja buat dropdown pas nyatet pengeluaran
        $expenseTypes = ExpenseType::where('is_active', true)->orderBy('name')->get();

        // Semua (termasuk nonaktif) buat modal "Kelola Jenis Pengeluaran"
        $allExpenseTypes = ExpenseType::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'totalBulanIni', 'expenseTypes', 'allExpenseTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        Expense::create($validated);

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $expense->update($validated);

        return back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}