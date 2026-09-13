<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'expense_type_id',
        'amount',
        'expense_date',
        'note',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}