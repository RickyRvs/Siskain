<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable dulu, biar bisa nambah kolom ke tabel yang udah ada isinya.
            // Di-unique-in setelah dibackfill di bawah.
            $table->string('username')->nullable()->after('name');
        });

        // Backfill: user lama belum punya username, isi otomatis dari bagian
        // sebelum "@" di email mereka. Kalau ada tabrakan (dua email beda
        // domain tapi local-part sama, mis. budi@toko-a.com & budi@toko-b.com),
        // ditambahin -2, -3, dst di belakangnya biar tetap unique.
        $usedUsernames = [];

        DB::table('users')->orderBy('id')->select('id', 'email')
            ->chunkById(100, function ($users) use (&$usedUsernames) {
                foreach ($users as $user) {
                    $base = Str::slug(Str::before($user->email, '@'), '');
                    $base = $base !== '' ? $base : 'user'.$user->id;

                    $candidate = $base;
                    $suffix = 2;

                    while (in_array($candidate, $usedUsernames, true)
                        || DB::table('users')->where('username', $candidate)->exists()) {
                        $candidate = $base.'-'.$suffix;
                        $suffix++;
                    }

                    $usedUsernames[] = $candidate;

                    DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};