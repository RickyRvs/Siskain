<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // 'bulanan' | 'tahunan' | 'lifetime'
            $table->string('subscription_plan')->default('bulanan')->after('is_active');
            $table->timestamp('subscription_started_at')->nullable()->after('subscription_plan');
            // null kalau lifetime (gak pernah kadaluarsa)
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_started_at');
        });

        // Tenant yang udah ada sebelum fitur ini dibuat: anggap mulai dari tanggal dibuat,
        // dan kasih tenggat 1 bulan ke depan supaya superadmin sadar buat diisi ulang manual.
        // Ganti/hapus blok ini kalau kalian mau nentuin sendiri tanggalnya lewat data seeder/manual.
        DB::table('tenants')->whereNull('subscription_started_at')->update([
            'subscription_started_at' => DB::raw('created_at'),
            'subscription_expires_at' => now()->addMonth(),
        ]);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['subscription_plan', 'subscription_started_at', 'subscription_expires_at']);
        });
    }
};