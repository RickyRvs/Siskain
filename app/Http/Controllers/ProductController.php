<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        $ingredients = Ingredient::orderBy('name')->get();

        // 'ingredients' di-eager-load karena kartu produk & modal edit di index.blade.php
        // butuh nampilin resep bahan baku tiap produk.
        $products = Product::with(['category', 'ingredients'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->low_stock, fn ($q) => $q->where('tracks_stock', true)->whereColumn('stock', '<=', 'min_stock'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $totalProducts = Product::count();

        // Cuma produk yang tracks_stock beneran dihitung buat kartu ringkasan low stock
        // & total nilai stok, biar Es Teh (stock selalu 0, tracks_stock false) gak ikut nyampur.
        $lowStockCount = Product::where('tracks_stock', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        $totalStockValue = Product::where('tracks_stock', true)
            ->sum(DB::raw('price_modal * stock'));

        return view('products.index', compact('products', 'categories', 'totalProducts', 'lowStockCount', 'totalStockValue', 'ingredients'));
    }

    public function create()
    {
        $categories = Category::all();
        $ingredients = Ingredient::orderBy('name')->get();
        return view('products.create', compact('categories', 'ingredients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku',
            'photo' => 'nullable|image|max:2048',
            'price_modal' => 'required|numeric|min:0',
            'price_jual' => 'required|numeric|min:0',
            'tracks_stock' => 'nullable|boolean',
            // stock/min_stock cuma wajib kalau produknya memang melacak stok sendiri
            'stock' => 'required_if:tracks_stock,1|nullable|integer|min:0',
            'min_stock' => 'required_if:tracks_stock,1|nullable|integer|min:0',
            // resep bahan baku (opsional, buat produk kayak Es Teh Susu)
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|distinct|exists:ingredients,id',
            'ingredients.*.qty_used' => 'required_with:ingredients|numeric|min:0.01',
        ]);

        $validated['tracks_stock'] = $request->boolean('tracks_stock');

        // Produk yang gak melacak stok (Es Teh dkk) gak butuh angka stok/min_stock,
        // dipaksa 0 supaya gak ada sisa nilai lama yang bikin bingung di laporan.
        if (!$validated['tracks_stock']) {
            $validated['stock'] = 0;
            $validated['min_stock'] = 0;
        }

        $ingredientsInput = $validated['ingredients'] ?? [];
        unset($validated['ingredients']);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create($validated);

        if ($product->tracks_stock && $product->stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'qty' => $product->stock,
                'note' => 'Stok awal produk',
                'user_id' => auth()->id(),
            ]);
        }

        if (!empty($ingredientsInput)) {
            $syncData = [];
            foreach ($ingredientsInput as $row) {
                $syncData[$row['ingredient_id']] = ['qty_used' => $row['qty_used']];
            }
            $product->ingredients()->sync($syncData);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $ingredients = Ingredient::orderBy('name')->get();
        $product->load('ingredients');
        return view('products.edit', compact('product', 'categories', 'ingredients'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'photo' => 'nullable|image|max:2048',
            'price_modal' => 'required|numeric|min:0',
            'price_jual' => 'required|numeric|min:0',
            'tracks_stock' => 'nullable|boolean',
            'min_stock' => 'required_if:tracks_stock,1|nullable|integer|min:0',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'required_with:ingredients|distinct|exists:ingredients,id',
            'ingredients.*.qty_used' => 'required_with:ingredients|numeric|min:0.01',
        ]);

        $validated['tracks_stock'] = $request->boolean('tracks_stock');

        // Stok gak diubah lewat form edit (sengaja, sama kayak perilaku lama) -
        // dia cuma berubah lewat menu penyesuaian stok / transaksi.
        // Kalau produk baru dimatiin tracking-nya, min_stock ikut di-nol-kan
        // karena gak relevan lagi buat produk tanpa stok.
        if (!$validated['tracks_stock']) {
            $validated['min_stock'] = 0;
        }

        $ingredientsInput = $validated['ingredients'] ?? [];
        unset($validated['ingredients']);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($validated);

        $syncData = [];
        foreach ($ingredientsInput as $row) {
            $syncData[$row['ingredient_id']] = ['qty_used' => $row['qty_used']];
        }
        $product->ingredients()->sync($syncData);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        if (!$product->tracks_stock) {
            return back()->with('error', 'Produk ini tidak melacak stok, penyesuaian stok tidak berlaku.');
        }

        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        if ($validated['type'] === 'out' && $product->stock < $validated['qty']) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        $product->stock += $validated['type'] === 'in' ? $validated['qty'] : -$validated['qty'];
        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'type' => $validated['type'],
            'qty' => $validated['qty'],
            'note' => $validated['note'] ?? null,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Stok berhasil diperbarui.');
    }
}