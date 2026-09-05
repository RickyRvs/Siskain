<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'subtotal',
        'discount',
        'tax',
        'additional_fee',
        'total',
        'payment_method',
        'status',
        'paid_amount',
        'change_amount',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'additional_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Selalu query langsung ke DB (bukan pakai $this->payments yang bisa saja
     * sudah ke-load lebih dulu dan jadi stale) supaya angka sisa piutang akurat,
     * termasuk saat dipanggil tepat setelah pembayaran baru dibuat di request yang sama.
     */
    public function sisaPiutang()
    {
        return $this->total - $this->payments()->sum('amount');
    }

    public function isLunas(): bool
    {
        return $this->status === 'lunas';
    }
}