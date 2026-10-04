<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create($validated);

        return redirect()
            ->route('products.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($validated);

        return redirect()
            ->route('products.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
        } catch (QueryException $e) {
            // 23000 = foreign key: kategori masih dipakai produk
            if ($e->getCode() === '23000') {
                return redirect()
                    ->route('products.index', ['tab' => 'kategori'])
                    ->with('error', 'Kategori "'.$category->name.'" tidak bisa dihapus karena masih dipakai produk.');
            }

            throw $e;
        }

        return redirect()
            ->route('products.index', ['tab' => 'kategori'])
            ->with('success', 'Kategori berhasil dihapus.');
    }
}