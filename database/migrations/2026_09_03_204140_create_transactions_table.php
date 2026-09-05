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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->string('invoice_number')->unique();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // kasir
        $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('subtotal', 15, 2)->default(0);
        $table->decimal('discount', 15, 2)->default(0);
        $table->decimal('tax', 15, 2)->default(0);
        $table->decimal('additional_fee', 15, 2)->default(0);
        $table->decimal('total', 15, 2)->default(0);
        $table->enum('payment_method', ['tunai', 'transfer', 'qris', 'lainnya'])->default('tunai');
        $table->enum('status', ['lunas', 'piutang', 'batal'])->default('lunas');
        $table->decimal('paid_amount', 15, 2)->default(0);
        $table->decimal('change_amount', 15, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
