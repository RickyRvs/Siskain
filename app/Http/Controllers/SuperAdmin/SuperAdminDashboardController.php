<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();

        $newTenantsThisMonth = Tenant::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $onlineUsers = User::whereNotNull('tenant_id')
            ->where('last_active_at', '>=', now()->subMinutes(5))
            ->with('tenant')
            ->get();

        $totalOmzet = Transaction::withoutGlobalScope('tenant')
            ->where('status', 'lunas')
            ->sum('total');

        $avgOmzetPerTenant = $activeTenants > 0 ? $totalOmzet / $activeTenants : 0;

        $recentHistories = \App\Models\TenantSettingsHistory::with('tenant', 'changedBy')
            ->latest()
            ->limit(8)
            ->get();

        $tenants = Tenant::withCount('users')
            ->latest()
            ->limit(5)
            ->get();

        return view('superadmin.dashboard', compact(
            'totalTenants', 'activeTenants', 'newTenantsThisMonth', 'onlineUsers',
            'totalOmzet', 'avgOmzetPerTenant', 'recentHistories', 'tenants'
        ));
    }
}