<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $ingredients = Ingredient::with('products')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->low_stock, fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $lowStockCount = Ingredient::whereColumn('stock', '<=', 'min_stock')->count();

        return view('ingredients.index', compact('ingredients', 'lowStockCount'));
    }

    public function create()
    {
        return view('ingredients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
        ]);

        $ingredient = Ingredient::create($validated);

        if ($ingredient->stock > 0) {
            IngredientStockMovement::create([
                'ingredient_id' => $ingredient->id,
                'type' => 'in',
                'qty' => $ingredient->stock,
                'note' => 'Stok awal bahan baku',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('ingredients.index')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(Ingredient $ingredient)
    {
        return view('ingredients.edit', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|numeric|min:0',
        ]);

        // Sama kayak Product: stok gak diubah lewat form edit, cuma lewat penyesuaian stok.
        $ingredient->update($validated);

        return redirect()->route('ingredients.index')->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(Ingredient $ingredient)
    {
        if ($ingredient->products()->exists()) {
            return back()->with('error', 'Bahan baku ini masih dipakai di resep produk, hapus dari resep dulu.');
        }

        $ingredient->delete();

        return redirect()->route('ingredients.index')->with('success', 'Bahan baku berhasil dihapus.');
    }

    public function adjustStock(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'qty' => 'required|numeric|min:0.01',
            'note' => 'nullable|string',
        ]);

        if ($validated['type'] === 'out' && $ingredient->stock < $validated['qty']) {
            return back()->with('error', 'Stok bahan tidak mencukupi.');
        }

        $ingredient->stock += $validated['type'] === 'in' ? $validated['qty'] : -$validated['qty'];
        $ingredient->save();

        IngredientStockMovement::create([
            'ingredient_id' => $ingredient->id,
            'type' => $validated['type'],
            'qty' => $validated['qty'],
            'note' => $validated['note'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Stok bahan baku berhasil diperbarui.');
    }
}