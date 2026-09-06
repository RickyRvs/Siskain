<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantUserController extends Controller
{
    /**
     * Tambah akun baru (owner tambahan atau kasir) buat sebuah tenant.
     * Kalau password dikosongkan, sistem generate password random dan
     * ditampilkan sekali di halaman detail (session flash), gak pernah disimpan mentah.
     */
    public function store(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:owner,kasir',
            'password' => 'nullable|string|min:8',
        ]);

        $plainPassword = $validated['password'] ?? Str::password(10);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($plainPassword),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        return back()
            ->with('success', "Akun {$user->name} berhasil dibuat.")
            ->with('revealed_password', $plainPassword)
            ->with('revealed_user_name', $user->name);
    }

    /**
     * Generate password baru buat sebuah akun dan tampilkan sekali (one-time reveal).
     * Password lama gak akan pernah bisa dilihat lagi karena disimpan dalam bentuk hash.
     */
    public function resetPassword(Request $request, Tenant $tenant, User $user)
    {
        abort_unless($user->tenant_id === $tenant->id, 404);

        $plainPassword = Str::password(10);

        $user->update(['password' => Hash::make($plainPassword)]);

        return back()
            ->with('success', "Password {$user->name} berhasil direset.")
            ->with('revealed_password', $plainPassword)
            ->with('revealed_user_name', $user->name);
    }

    /**
     * Hapus sebuah akun. Owner terakhir di sebuah tenant gak boleh dihapus
     * (kalau mau ganti owner, tambah owner baru dulu baru hapus yang lama).
     */
    public function destroy(Tenant $tenant, User $user)
    {
        abort_unless($user->tenant_id === $tenant->id, 404);

        if ($user->role === 'owner' && $tenant->users()->where('role', 'owner')->count() <= 1) {
            return back()->with('error', 'Tidak bisa menghapus owner satu-satunya. Tambah owner baru dulu.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }
}