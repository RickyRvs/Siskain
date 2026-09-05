<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // true  = produk kayak Aqua botol, stok dilacak & dikurangi tiap transaksi
            // false = produk kayak Es Teh, dibikin on-demand, gak ada konsep "stok produk"
            //         (kalau dia butuh bahan baku, pakai tabel ingredients + product_ingredient)
            $table->boolean('tracks_stock')->default(true)->after('has_variant');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tracks_stock');
        });
    }
};