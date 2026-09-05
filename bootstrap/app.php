<?php

use App\Http\Middleware\EnsureMenuPermission;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\UpdateLastActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'menu' => EnsureMenuPermission::class,
        ]);

        $middleware->web(append: [
            IdentifyTenant::class,
            UpdateLastActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();