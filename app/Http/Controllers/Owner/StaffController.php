<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = User::where('tenant_id', $request->user()->tenant_id)
            ->where('role', 'kasir')
            ->latest()
            ->paginate(10);

        return view('owner.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('owner.staff.create', ['menus' => config('menus')]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(config('menus'))),
        ]);

        User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'kasir',
            'permissions' => $validated['permissions'] ?? [],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('owner.staff.index')->with('success', 'Akun kasir berhasil ditambahkan.');
    }

    public function edit(User $staff)
    {
        $this->authorizeStaff($staff);

        return view('owner.staff.edit', ['staff' => $staff, 'menus' => config('menus')]);
    }

    public function update(Request $request, User $staff)
    {
        $this->authorizeStaff($staff);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'password' => 'nullable|string|min:8',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', array_keys(config('menus'))),
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->permissions = $validated['permissions'] ?? [];

        if (!empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }

        $staff->save();

        return redirect()->route('owner.staff.index')->with('success', 'Akun kasir berhasil diperbarui.');
    }

    public function destroy(User $staff)
    {
        $this->authorizeStaff($staff);

        $staff->delete();

        return redirect()->route('owner.staff.index')->with('success', 'Akun kasir berhasil dihapus.');
    }

    /**
     * User model gak pakai BelongsToTenant trait (itu cuma buat data bisnis),
     * jadi route model binding {staff} gak otomatis kefilter tenant.
     * Makanya dicek manual di sini biar owner gak bisa ngutak-ngatik kasir tenant lain.
     */
    private function authorizeStaff(User $staff): void
    {
        if ($staff->tenant_id !== auth()->user()->tenant_id || $staff->role !== 'kasir') {
            abort(403);
        }
    }
}