<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Sinkronkan locale Carbon (format tanggal: nama hari/bulan, diffForHumans)
        // dengan locale aplikasi (APP_LOCALE=id di .env). Tanpa ini, Carbon
        // tetap pakai locale default 'en' walaupun app.locale sudah 'id'.
        Carbon::setLocale(config('app.locale'));
    }
}