<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'title', 'logo_path', 'primary_color', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}