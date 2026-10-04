<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();

            $table->unsignedBigInteger('opened_by')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();

            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();

            // Tanggal bisnis = tanggal pas toko dibuka. Sesi Senin 18:00 s/d Selasa 01:00
            // tetap dihitung sebagai "hari Senin".
            $table->date('business_date');

            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('closing_cash_counted', 15, 2)->nullable();
            $table->decimal('cash_difference', 15, 2)->nullable();
            $table->string('note', 255)->nullable();

            // Penanda "sedang buka": diisi 1 pas dibuka, di-NULL-kan pas ditutup.
            // Unique (tenant_id, open_marker) bikin database sendiri yang menolak
            // kalau ada dua sesi terbuka bersamaan di tenant yang sama
            // (NULL boleh banyak, jadi sesi yang sudah tutup gak bentrok).
            $table->unsignedTinyInteger('open_marker')->nullable();
            $table->unique(['tenant_id', 'open_marker'], 'store_sessions_one_open_per_tenant');

            $table->timestamps();

            $table->index(['tenant_id', 'opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_sessions');
    }
};