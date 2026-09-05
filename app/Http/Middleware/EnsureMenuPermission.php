<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuPermission
{
    public function handle(Request $request, Closure $next, string $menu): Response
    {
        if (!$request->user() || !$request->user()->canAccessMenu($menu)) {
            abort(403, 'Kamu tidak punya akses ke menu ini.');
        }

        return $next($request);
    }
}