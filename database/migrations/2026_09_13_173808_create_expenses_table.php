<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category'); // sewa, operasional, bahan_non_resep, gaji, dll (bebas string, bukan enum kaku)
            $table->string('name'); // "Sewa lapak bulan Sept", "Es batu"
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};