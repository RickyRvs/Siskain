<?php
// database/migrations/2026_09_05_000004_add_tenant_id_to_business_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'categories',
        'products',
        'product_variants',
        'ingredients',
        'customers',
        'transactions',
        'transaction_items',
        'stock_movements',
        'ingredient_stock_movements',
        'payments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    // Nullable dulu biar aman buat data lama, nanti diisi manual/seeder
                    // lalu boleh diubah ke not-null kalau semua row udah punya tenant.
                    $table->foreignId('tenant_id')->nullable()->after('id')
                        ->constrained('tenants')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('tenant_id');
                });
            }
        }
    }
};