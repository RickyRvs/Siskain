<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    public const PLAN_BULANAN = 'bulanan';
    public const PLAN_TAHUNAN = 'tahunan';
    public const PLAN_LIFETIME = 'lifetime';

    public const PLANS = [
        self::PLAN_BULANAN => 'Bulanan',
        self::PLAN_TAHUNAN => 'Tahunan',
        self::PLAN_LIFETIME => 'Lifetime',
    ];

    protected $fillable = [
        'slug', 'name', 'title', 'logo_path', 'primary_color', 'is_active',
        'subscription_plan', 'subscription_started_at', 'subscription_expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_started_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settingsHistories(): HasMany
    {
        return $this->hasMany(TenantSettingsHistory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Update field setting + catat histori kalau nilainya berubah.
     * Dipakai dari SettingController biar histori otomatis kecatat.
     * Cocok untuk field bertipe string sederhana (name, title, primary_color, subscription_plan, dst).
     * Untuk field tanggal/boolean, update langsung via ->update() (lihat TenantController@update).
     */
    public function updateSetting(string $field, ?string $newValue, ?int $changedBy = null): void
    {
        $oldValue = $this->{$field};

        if ($oldValue === $newValue) {
            return;
        }

        $this->update([$field => $newValue]);

        $this->settingsHistories()->create([
            'changed_by' => $changedBy,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /**
     * Label plan yang rapi buat ditampilkan ("Bulanan", "Tahunan", "Lifetime").
     */
    public function getSubscriptionPlanLabelAttribute(): string
    {
        return self::PLANS[$this->subscription_plan] ?? ucfirst($this->subscription_plan ?? 'Bulanan');
    }

    /**
     * Lifetime dianggap gak pernah kadaluarsa. Plan lain kadaluarsa kalau
     * expires_at ada isinya dan udah lewat.
     */
    public function getIsSubscriptionExpiredAttribute(): bool
    {
        if ($this->subscription_plan === self::PLAN_LIFETIME) {
            return false;
        }

        return $this->subscription_expires_at !== null && $this->subscription_expires_at->isPast();
    }

    /**
     * Sisa hari sampai kadaluarsa. Null berarti lifetime atau belum diatur.
     * Bisa negatif kalau udah lewat (dipakai buat nampilin "terlambat 3 hari").
     */
    public function getSubscriptionDaysLeftAttribute(): ?int
    {
        if ($this->subscription_plan === self::PLAN_LIFETIME || $this->subscription_expires_at === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->subscription_expires_at->copy()->startOfDay(), false);
    }

    /**
     * Info badge siap-pakai buat view: ['label' => ..., 'bg' => ..., 'text' => ...].
     * Satu sumber kebenaran biar warna/logic konsisten di index, show, dashboard, dll.
     */
    public function getSubscriptionBadgeAttribute(): array
    {
        if ($this->subscription_plan === self::PLAN_LIFETIME) {
            return ['label' => 'Lifetime', 'bg' => 'bg-[#EFE8F5]', 'text' => 'text-[#6B4E9A]'];
        }

        if ($this->subscription_expires_at === null) {
            return ['label' => 'Belum diatur', 'bg' => 'bg-[#F2F2F2]', 'text' => 'text-[#8A8272]'];
        }

        if ($this->is_subscription_expired) {
            return ['label' => 'Kadaluarsa', 'bg' => 'bg-[#FBEAE6]', 'text' => 'text-[#B5482E]'];
        }

        $daysLeft = $this->subscription_days_left;

        if ($daysLeft !== null && $daysLeft <= 7) {
            $label = $daysLeft <= 0 ? 'Berakhir hari ini' : ($daysLeft === 1 ? '1 hari lagi' : "{$daysLeft} hari lagi");
            return ['label' => $label, 'bg' => 'bg-[#FBF1DD]', 'text' => 'text-[#B5842A]'];
        }

        return [
            'label' => $this->subscription_plan_label,
            'bg' => 'bg-[#EAF0F3]',
            'text' => 'text-[#1B6E6E]',
        ];
    }

    /**
     * Tenant berbayar (bulanan/tahunan, bukan lifetime) yang mau habis masa aktifnya
     * dalam $days hari ke depan. Dipakai buat widget "Akan Berakhir" di dashboard.
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('subscription_plan', '!=', self::PLAN_LIFETIME)
            ->whereNotNull('subscription_expires_at')
            ->whereBetween('subscription_expires_at', [now(), now()->addDays($days)->endOfDay()]);
    }

    /**
     * Tenant berbayar yang udah lewat masa aktifnya.
     */
    public function scopeExpiredSubscription($query)
    {
        return $query->where('subscription_plan', '!=', self::PLAN_LIFETIME)
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<', now());
    }
}