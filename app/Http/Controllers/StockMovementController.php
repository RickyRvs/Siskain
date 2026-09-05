<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientStockMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $tenantContext = app(TenantContext::class);
        $isGlobal = !$tenantContext->check(); // superadmin tanpa impersonate = lihat semua
        $tenantId = $tenantContext->id();

        // Union dua sumber histori stok (produk & bahan baku) jadi satu timeline,
        // biar "Riwayat Stok" gak cuma nunjukin produk padahal bahan baku juga
        // punya kartu stok sendiri (ingredient_stock_movements).
        //
        // FIX: kedua query ini pakai DB::table() langsung, jadi gak kena global
        // scope tenant dari model StockMovement/IngredientStockMovement.
        // Difilter manual di sini.
        $productMovements = DB::table('stock_movements as sm')
            ->join('products as p', 'p.id', '=', 'sm.product_id')
            ->leftJoin('product_variants as pv', 'pv.id', '=', 'sm.product_variant_id')
            ->join('users as u', 'u.id', '=', 'sm.user_id')
            ->when(!$isGlobal, fn ($q) => $q->where('sm.tenant_id', $tenantId))
            ->select([
                'sm.id',
                DB::raw("'product' as source_type"),
                'sm.product_id as item_id',
                'p.name as item_name',
                'pv.name as variant_name',
                DB::raw('NULL as unit'),
                'sm.type',
                'sm.qty',
                'sm.note',
                'u.name as user_name',
                'sm.created_at',
            ]);

        $ingredientMovements = DB::table('ingredient_stock_movements as ism')
            ->join('ingredients as i', 'i.id', '=', 'ism.ingredient_id')
            ->join('users as u', 'u.id', '=', 'ism.user_id')
            ->when(!$isGlobal, fn ($q) => $q->where('ism.tenant_id', $tenantId))
            ->select([
                'ism.id',
                DB::raw("'ingredient' as source_type"),
                'ism.ingredient_id as item_id',
                'i.name as item_name',
                DB::raw('NULL as variant_name'),
                'i.unit',
                'ism.type',
                'ism.qty',
                'ism.note',
                'u.name as user_name',
                'ism.created_at',
            ]);

        $union = $productMovements->unionAll($ingredientMovements);

        $movements = DB::query()
            ->fromSub($union, 'm')
            ->when($request->source_type, fn ($q) => $q->where('m.source_type', $request->source_type))
            ->when($request->item, function ($q) use ($request) {
                // Value filter format: "product-3" atau "ingredient-5"
                [$type, $id] = array_pad(explode('-', $request->item, 2), 2, null);
                if ($type && $id) {
                    $q->where('m.source_type', $type)->where('m.item_id', $id);
                }
            })
            ->when($request->type, fn ($q) => $q->where('m.type', $request->type))
            ->when($request->date, fn ($q) => $q->whereDate('m.created_at', $request->date))
            ->orderByDesc('m.created_at')
            ->orderByDesc('m.id')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        // Statistik hari ini gabungan produk + bahan baku
        $today = today();
        $stats = [
            'today_in' => StockMovement::whereDate('created_at', $today)->where('type', 'in')->sum('qty')
                + IngredientStockMovement::whereDate('created_at', $today)->where('type', 'in')->sum('qty'),
            'today_out' => StockMovement::whereDate('created_at', $today)->where('type', 'out')->sum('qty')
                + IngredientStockMovement::whereDate('created_at', $today)->where('type', 'out')->sum('qty'),
            'today_count' => StockMovement::whereDate('created_at', $today)->count()
                + IngredientStockMovement::whereDate('created_at', $today)->count(),
        ];

        return view('stock-movements.index', compact('movements', 'products', 'ingredients', 'stats'));
    }

    public function create()
    {
        $products = Product::where('tracks_stock', true)->with('variants')->orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('stock-movements.create', compact('products', 'ingredients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:product,ingredient',
            'product_id' => 'required_if:source_type,product|nullable|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'ingredient_id' => 'required_if:source_type,ingredient|nullable|exists:ingredients,id',
            'type' => 'required|in:in,out',
            'qty' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validated['source_type'] === 'product') {
            if ($validated['product_variant_id'] ?? null) {
                $target = ProductVariant::lockForUpdate()->findOrFail($validated['product_variant_id']);
            } else {
                $target = Product::lockForUpdate()->findOrFail($validated['product_id']);
            }

            if ($validated['type'] === 'out' && $target->stock < $validated['qty']) {
                return back()->withInput()->with('error', 'Stok produk tidak mencukupi untuk pengurangan ini.');
            }

            $target->stock += $validated['type'] === 'in' ? $validated['qty'] : -$validated['qty'];
            $target->save();

            StockMovement::create([
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['product_variant_id'] ?? null,
                'type' => $validated['type'],
                'qty' => $validated['qty'],
                'note' => $validated['note'] ?? 'Penyesuaian stok manual',
                'user_id' => auth()->id(),
            ]);
        } else {
            $ingredient = Ingredient::lockForUpdate()->findOrFail($validated['ingredient_id']);

            if ($validated['type'] === 'out' && $ingredient->stock < $validated['qty']) {
                return back()->withInput()->with('error', 'Stok bahan baku tidak mencukupi untuk pengurangan ini.');
            }

            $ingredient->stock += $validated['type'] === 'in' ? $validated['qty'] : -$validated['qty'];
            $ingredient->save();

            IngredientStockMovement::create([
                'ingredient_id' => $ingredient->id,
                'type' => $validated['type'],
                'qty' => $validated['qty'],
                'note' => $validated['note'] ?? 'Penyesuaian stok manual',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('stock-movements.index')->with('success', 'Stok berhasil disesuaikan.');
    }

    public function show(Request $request, string $id)
    {
        $source = $request->query('source', 'product');

        if ($source === 'ingredient') {
            $movement = IngredientStockMovement::with(['ingredient', 'user'])->findOrFail($id);
            return view('stock-movements.show', ['movement' => $movement, 'source' => 'ingredient']);
        }

        $movement = StockMovement::with(['product', 'variant', 'user'])->findOrFail($id);
        return view('stock-movements.show', ['movement' => $movement, 'source' => 'product']);
    }

    public function edit(string $id)
    {
        abort(403, 'Riwayat stok tidak dapat diedit.');
    }

    public function update(Request $request, string $id)
    {
        abort(403, 'Riwayat stok tidak dapat diedit.');
    }

    public function destroy(string $id)
    {
        abort(403, 'Riwayat stok tidak dapat dihapus.');
    }
}