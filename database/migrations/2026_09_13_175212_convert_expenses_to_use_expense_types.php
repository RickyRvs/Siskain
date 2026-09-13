<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom baru dulu (nullable sementara), biar data lama masih valid
        //    selama proses backfill di bawah berjalan.
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_type_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // 2. Backfill: tiap kombinasi unik (tenant_id, category) di data expenses lama
        //    dijadikan satu baris expense_types baru. `category` yang dipakai (bukan
        //    `name`) karena category sifatnya reusable/berulang (mis. "Sewa", "Gaji"),
        //    sedangkan `name` per baris isinya keterangan spesifik kayak
        //    "Sewa lapak bulan Sept" yang gak cocok jadi "tipe" yang dipakai berulang.
        $combinations = DB::table('expenses')
            ->select('tenant_id', 'category')
            ->distinct()
            ->get();

        foreach ($combinations as $combo) {
            $expenseTypeId = DB::table('expense_types')
                ->where('tenant_id', $combo->tenant_id)
                ->where('name', $combo->category)
                ->value('id');

            if (!$expenseTypeId) {
                $expenseTypeId = DB::table('expense_types')->insertGetId([
                    'tenant_id' => $combo->tenant_id,
                    'name' => $combo->category,
                    'default_amount' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('expenses')
                ->where('tenant_id', $combo->tenant_id)
                ->where('category', $combo->category)
                ->update(['expense_type_id' => $expenseTypeId]);
        }

        // 3. Kolom `name` lama (keterangan spesifik per transaksi, mis. "Sewa lapak
        //    bulan Sept") digabung ke `note` per baris, biar informasinya gak hilang
        //    walau kolom `name` nanti didrop. Dilakukan per-row (bukan per kombinasi)
        //    karena isinya unik tiap baris, beda sama `category` di atas.
        DB::table('expenses')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $mergedNote = trim($row->name . (empty($row->note) ? '' : ' — ' . $row->note));

                DB::table('expenses')
                    ->where('id', $row->id)
                    ->update(['note' => $mergedNote]);
            }
        });

        // 4. Semua baris udah pasti punya expense_type_id di titik ini (setiap
        //    kombinasi tenant_id+category pasti kebentuk expense_types-nya di
        //    langkah 2), jadi kolom lama aman dibuang & expense_type_id dikunci wajib.
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['category', 'name']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_type_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        // Kembalikan kolom lama (best-effort — detail gabungan di `note` gak bisa
        // dipisah balik ke `category`/`name` yang persis sama seperti semula).
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('user_id');
            $table->string('name')->nullable()->after('category');
        });

        DB::table('expenses')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                if (!$row->expense_type_id) {
                    continue;
                }

                $typeName = DB::table('expense_types')->where('id', $row->expense_type_id)->value('name');

                DB::table('expenses')
                    ->where('id', $row->id)
                    ->update([
                        'category' => $typeName,
                        'name' => $typeName,
                    ]);
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_type_id');
        });
    }
};