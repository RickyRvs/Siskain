<?php
// database/migrations/2026_09_05_090001_fix_products_sku_unique_per_tenant.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Nama index dikonfirmasi dari sqlite_master: products_sku_unique
            $table->dropUnique('products_sku_unique');

            $table->unique(['tenant_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'sku']);
            $table->unique('sku');
        });
    }
};