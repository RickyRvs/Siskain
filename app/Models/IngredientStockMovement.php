<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientStockMovement extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'ingredient_id',
        'type',
        'qty',
        'note',
        'user_id',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}