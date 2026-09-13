<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Referensi unik yang dikirim ke DOKU (partnerReferenceNo), dipakai buat
            // mencocokkan notifikasi/webhook yang masuk dengan transaksi ini.
            $table->string('doku_reference')->nullable()->unique()->after('payment_method');

            // Channel spesifik yang dipilih: QRIS, VA_BCA, VA_BNI, OVO, DANA, dll.
            $table->string('doku_channel')->nullable()->after('doku_reference');

            // Kode pembayaran yang ditampilkan ke customer: nomor VA atau string QR.
            $table->text('doku_payment_code')->nullable()->after('doku_channel');

            // Kapan kode pembayaran ini expired, buat auto-cancel kalau lewat.
            $table->timestamp('doku_expired_at')->nullable()->after('doku_payment_code');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['doku_reference', 'doku_channel', 'doku_payment_code', 'doku_expired_at']);
        });
    }
};