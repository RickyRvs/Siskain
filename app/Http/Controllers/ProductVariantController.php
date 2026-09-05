<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product)
    {
        $variants = $product->variants()->latest()->paginate(10);

        return view('product-variants.index', compact('product', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Product $product)
    {
        return view('product-variants.create', compact('product'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:product_variants,sku',
            'price_modal' => 'required|numeric|min:0',
            'price_jual' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $variant = $product->variants()->create($validated);

        if (!$product->has_variant) {
            $product->update(['has_variant' => true]);
        }

        if ($variant->stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'type' => 'in',
                'qty' => $variant->stock,
                'note' => 'Stok awal varian ' . $variant->name,
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('products.variants.index', $product)->with('success', 'Varian berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, ProductVariant $variant)
    {
        return view('product-variants.show', compact('product', 'variant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product, ProductVariant $variant)
    {
        return view('product-variants.edit', compact('product', 'variant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:product_variants,sku,' . $variant->id,
            'price_modal' => 'required|numeric|min:0',
            'price_jual' => 'required|numeric|min:0',
        ]);

        $variant->update($validated);

        return redirect()->route('products.variants.index', $product)->with('success', 'Varian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        $variant->delete();

        if ($product->variants()->count() === 0) {
            $product->update(['has_variant' => false]);
        }

        return redirect()->route('products.variants.index', $product)->with('success', 'Varian berhasil dihapus.');
    }
}