<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'unit',
        'stock',
        'min_stock',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
    ];

    /**
     * Produk-produk yang resepnya pakai bahan ini.
     * qty_used = jumlah bahan yang dipakai untuk 1 unit produk terkait.
     *
     * Nama tabel pivot dikasih eksplisit ('product_ingredient') karena default
     * tebakan Laravel (alfabetis: ingredient_product) beda sama nama tabel
     * yang dibikin di migration.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredient')
            ->withPivot('qty_used')
            ->withTimestamps();
    }

    public function stockMovements()
    {
        return $this->hasMany(IngredientStockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}