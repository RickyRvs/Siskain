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
            ->when($request->status === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->when($request->status === 'kadaluarsa', fn ($q) => $q->expiredSubscription())
            ->latest()
            ->paginate(9)
            ->withQueryString();

        // Ringkasan buat header halaman, dihitung dari semua tenant (bukan cuma yang lagi ditampilkan)
        $summary = [
            'total' => Tenant::count(),
            'aktif' => Tenant::where('is_active', true)->count(),
            'nonaktif' => Tenant::where('is_active', false)->count(),
            'kadaluarsa' => Tenant::expiredSubscription()->count(),
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
        $validated = $this->validateTenant($request);

        DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
                'name' => $validated['name'],
                'title' => $validated['title'] ?? null,
                'primary_color' => $validated['primary_color'] ?? '#0F2E2B',
                'subscription_plan' => $validated['subscription_plan'],
                'subscription_started_at' => now(),
                'subscription_expires_at' => $validated['subscription_plan'] === Tenant::PLAN_LIFETIME
                    ? null
                    : $validated['subscription_expires_at'],
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

    /**
     * Halaman detail: profil usaha, status langganan, dan manajemen akun
     * (lihat semua akun, reset password, tambah akun, hapus akun).
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['users' => fn ($q) => $q->orderByRaw("role = 'owner' desc")->orderBy('name')]);

        $tenantOmzet = $tenant->transactions()
            ->withoutGlobalScope('tenant')
            ->where('status', 'lunas')
            ->sum('total');

        $histories = $tenant->settingsHistories()->with('changedBy')->latest()->limit(10)->get();

        return view('superadmin.tenants.show', compact('tenant', 'tenantOmzet', 'histories'));
    }

    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $this->validateTenant($request, $tenant);

        // Field string sederhana: dicatat ke histori otomatis lewat updateSetting.
        foreach (['name', 'title', 'primary_color', 'subscription_plan'] as $field) {
            $tenant->updateSetting($field, $validated[$field] ?? null, auth()->id());
        }

        // is_active & subscription_expires_at bukan string biasa (boolean/tanggal),
        // jadi diupdate langsung tanpa lewat updateSetting (gak ikut kecatat di histori).
        $tenant->update([
            'is_active' => $request->boolean('is_active'),
            'subscription_expires_at' => $validated['subscription_plan'] === Tenant::PLAN_LIFETIME
                ? null
                : ($validated['subscription_expires_at'] ?? $tenant->subscription_expires_at),
        ]);

        return redirect()->route('superadmin.tenants.index')->with('success', 'Usaha berhasil diperbarui.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete(); // cascade ke users & data bisnis, lihat foreign key di migration

        return redirect()->route('superadmin.tenants.index')->with('success', 'Usaha berhasil dihapus.');
    }

    /**
     * Perpanjang langganan: +1 bulan (plan bulanan) atau +1 tahun (plan tahunan),
     * dihitung dari tanggal expired lama kalau masih aktif, atau dari hari ini kalau udah lewat
     * (biar tenant gak "untung" dari keterlambatan perpanjangan, tapi juga gak kehilangan sisa waktu berjalan).
     */
    public function renewSubscription(Tenant $tenant)
    {
        if ($tenant->subscription_plan === Tenant::PLAN_LIFETIME) {
            return back()->with('error', 'Usaha ini sudah lifetime, tidak perlu diperpanjang.');
        }

        $oldExpiry = $tenant->subscription_expires_at;
        $base = ($oldExpiry && $oldExpiry->isFuture()) ? $oldExpiry->copy() : now();

        $newExpiry = $tenant->subscription_plan === Tenant::PLAN_TAHUNAN
            ? $base->addYear()
            : $base->addMonth();

        $tenant->update(['subscription_expires_at' => $newExpiry]);

        $tenant->settingsHistories()->create([
            'changed_by' => auth()->id(),
            'field' => 'subscription_expires_at',
            'old_value' => $oldExpiry?->toDateString() ?? '(belum ada)',
            'new_value' => $newExpiry->toDateString(),
        ]);

        return back()->with('success', "Langganan {$tenant->name} diperpanjang sampai " . $newExpiry->translatedFormat('d M Y') . '.');
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

    /**
     * Validasi bersama untuk store() & update(). Saat update, email owner tidak divalidasi
     * di sini karena akun owner dikelola lewat TenantUserController (halaman detail).
     */
    private function validateTenant(Request $request, ?Tenant $tenant = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'subscription_plan' => 'required|in:' . implode(',', array_keys(Tenant::PLANS)),
            'subscription_expires_at' => 'nullable|date|required_unless:subscription_plan,lifetime',
        ];

        if (!$tenant) {
            $rules['owner_name'] = 'required|string|max:255';
            $rules['owner_email'] = 'required|email|unique:users,email';
            $rules['owner_password'] = 'required|string|min:8';
        }

        return $request->validate($rules);
    }
}