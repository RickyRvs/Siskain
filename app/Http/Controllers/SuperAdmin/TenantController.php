<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::withCount('users')
            ->withSum('transactions', 'total') // butuh scope tenant di-bypass, lihat catatan di bawah
            // ambil 1 akun owner tiap tenant buat ditampilkan di kartu
            ->with(['users' => fn ($q) => $q->where('role', 'owner')->oldest()->limit(1)])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('is_active', $request->status === 'aktif'))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        // Ringkasan buat header halaman, dihitung dari semua tenant (bukan cuma yang lagi ditampilkan)
        $summary = [
            'total' => Tenant::count(),
            'aktif' => Tenant::where('is_active', true)->count(),
            'nonaktif' => Tenant::where('is_active', false)->count(),
            'omzet' => (float) DB::table('transactions')->where('status', 'lunas')->sum('total'),
        ];

        return view('superadmin.tenants.index', compact('tenants', 'summary'));
    }

    public function create()
    {
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_password' => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
                'name' => $validated['name'],
                'title' => $validated['title'] ?? null,
                'primary_color' => $validated['primary_color'] ?? '#0F2E2B',
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'role' => 'owner',
                'email_verified_at' => now(),
            ]);
        });

        return redirect()->route('superadmin.tenants.index')->with('success', 'Usaha & akun owner berhasil dibuat.');
    }

    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
        ]);

        // Pakai updateSetting biar histori kecatat otomatis (lihat Tenant model)
        foreach (['name', 'title', 'primary_color'] as $field) {
            $tenant->updateSetting($field, $validated[$field] ?? null, auth()->id());
        }

        $tenant->update(['is_active' => $request->boolean('is_active')]);

        return redirect()->route('superadmin.tenants.index')->with('success', 'Usaha berhasil diperbarui.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete(); // cascade ke users & data bisnis, lihat foreign key di migration

        return redirect()->route('superadmin.tenants.index')->with('success', 'Usaha berhasil dihapus.');
    }

    /**
     * Superadmin "masuk" ke tenant tertentu buat lihat data/troubleshoot,
     * tanpa perlu tau password owner-nya.
     */
    public function impersonate(Request $request, Tenant $tenant)
    {
        $request->session()->put('impersonate_tenant_id', $tenant->id);

        return redirect()->route('dashboard')->with('success', "Sekarang lagi lihat data milik {$tenant->name}.");
    }

    public function stopImpersonate(Request $request)
    {
        $request->session()->forget('impersonate_tenant_id');

        return redirect()->route('superadmin.dashboard')->with('success', 'Kembali ke mode superadmin.');
    }
}