<?php

namespace App\Traits;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $context = app(TenantContext::class);

            // Superadmin tanpa tenant aktif = lihat semua tenant (buat monitoring).
            if ($context->check()) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $context->id());
            }
        });

        static::creating(function (Model $model) {
            $context = app(TenantContext::class);

            if (empty($model->tenant_id) && $context->check()) {
                $model->tenant_id = $context->id();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Buat kebutuhan superadmin ngambil data lintas tenant secara eksplisit.
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}