<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price_modal',
        'price_jual',
        'stock',
    ];

    protected $casts = [
        'price_modal' => 'decimal:2',
        'price_jual' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}