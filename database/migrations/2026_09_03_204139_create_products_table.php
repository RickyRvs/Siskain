<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('sku')->unique()->nullable();
        $table->string('photo')->nullable();
        $table->decimal('price_modal', 15, 2)->default(0);
        $table->decimal('price_jual', 15, 2)->default(0);
        $table->integer('stock')->default(0);
        $table->integer('min_stock')->default(0);
        $table->boolean('has_variant')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
