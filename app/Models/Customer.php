<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['name', 'phone', 'address'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function totalPiutang()
    {
        return $this->transactions()
            ->where('status', 'piutang')
            ->get()
            ->sum(fn ($t) => $t->total - $t->payments->sum('amount'));
    }
}