<?php
// database/migrations/2026_09_14_171758_create_invoice_counters_and_fix_unique.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_counters')) {
            Schema::create('invoice_counters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->date('date');
                $table->unsignedInteger('last_number')->default(0);
                $table->timestamps();

                $table->unique(['tenant_id', 'date']);
            });
        }

        // Drop unique constraint lama yang cuma di invoice_number doang
        // (rawan collision antar tenant), ganti jadi unique gabungan.
        $indexes = DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'transactions_invoice_number_unique'");
        if (!empty($indexes)) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropUnique('transactions_invoice_number_unique');
            });
        }

        $indexes2 = DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'transactions_tenant_id_invoice_number_unique'");
        if (empty($indexes2)) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unique(['tenant_id', 'invoice_number']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'invoice_number']);
            $table->unique('invoice_number');
        });

        Schema::dropIfExists('invoice_counters');
    }
};