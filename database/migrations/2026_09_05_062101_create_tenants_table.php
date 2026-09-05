<?php
// database/migrations/2026_09_05_000001_create_tenants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // identifier internal, gak dipakai di URL
            $table->string('name'); // nama sistem, dipakai gantiin config('app.name')
            $table->string('title')->nullable(); // judul besar/tagline di landing/login
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#0F2E2B'); // hex
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};