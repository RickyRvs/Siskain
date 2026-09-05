<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
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
    ];

    protected $casts = [
        'price_modal' => 'decimal:2',
        'price_jual' => 'decimal:2',
        'has_variant' => 'boolean',
        'tracks_stock' => 'boolean',
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

    /**
     * Resep produk ini: bahan baku apa saja yang kepakai tiap 1 unit produk terjual.
     * Contoh: Es Teh Susu -> Susu (qty_used = 100ml).
     * Produk yang gak butuh bahan baku (misal Aqua botol) relasi ini kosong.
     *
     * Nama tabel pivot dikasih eksplisit ('product_ingredient') karena default
     * tebakan Laravel (alfabetis: ingredient_product) beda sama nama tabel
     * yang dibikin di migration.
     */
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredient')
            ->withPivot('qty_used')
            ->withTimestamps();
    }

    /**
     * Cuma produk dengan tracks_stock=true yang dianggap "low stock",
     * produk kayak Es Teh (tracks_stock=false) gak pernah masuk hitungan ini.
     */
    public function isLowStock(): bool
    {
        return $this->tracks_stock && $this->stock <= $this->min_stock;
    }
}