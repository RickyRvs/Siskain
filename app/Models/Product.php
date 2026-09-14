<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'photo',
        'price_modal',
        'price_jual',
        'stock',
        'min_stock',
        'has_variant',
        'tracks_stock',
        'is_active',
    ];

    protected $casts = [
        'price_modal' => 'decimal:2',
        'price_jual' => 'decimal:2',
        'has_variant' => 'boolean',
        'tracks_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredient')
            ->withPivot('qty_used')
            ->withTimestamps();
    }

    public function isLowStock(): bool
    {
        return $this->tracks_stock && $this->stock <= $this->min_stock;
    }

    /**
     * Scope buat ambil produk yang masih dijual aja.
     * Dipakai di katalog kasir supaya produk yang dinonaktifkan
     * otomatis ngilang tanpa perlu diulang-ulang query where-nya.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}