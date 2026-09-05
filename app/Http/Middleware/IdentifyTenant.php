<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            if ($user->role !== 'superadmin' && $user->tenant) {
                app(TenantContext::class)->set($user->tenant);
            } elseif ($user->role === 'superadmin' && $request->session()->has('impersonate_tenant_id')) {
                // Superadmin lagi "intip" tenant tertentu (misal buka menu produk milik usaha X)
                $tenant = \App\Models\Tenant::find($request->session()->get('impersonate_tenant_id'));
                app(TenantContext::class)->set($tenant);
            }
            // superadmin tanpa impersonate: TenantContext tetap null -> query gak difilter
        }

        return $next($request);
    }
}