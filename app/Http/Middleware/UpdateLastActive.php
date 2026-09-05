<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Update paling banyak tiap 1 menit biar gak nulis DB tiap request.
        if ($user && (!$user->last_active_at || $user->last_active_at->lt(now()->subMinute()))) {
            $user->timestamps = false; // biar updated_at gak ikut kesentuh tiap detik
            $user->forceFill(['last_active_at' => now()])->save();
        }

        return $next($request);
    }
}