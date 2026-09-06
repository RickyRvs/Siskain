<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\SuperAdmin\TenantUserController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\Owner\SettingController;
use App\Http\Controllers\Owner\StaffController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| PANEL SUPERADMIN — cuma role superadmin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{tenant}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');
        Route::post('impersonate/stop', [TenantController::class, 'stopImpersonate'])->name('impersonate.stop');

        // Perpanjang langganan (tombol "Perpanjang" di halaman detail tenant)
        Route::post('tenants/{tenant}/renew', [TenantController::class, 'renewSubscription'])->name('tenants.renew');

        // Manajemen akun (owner & kasir) di dalam sebuah tenant
        Route::post('tenants/{tenant}/users', [TenantUserController::class, 'store'])->name('tenants.users.store');
        Route::post('tenants/{tenant}/users/{user}/reset-password', [TenantUserController::class, 'resetPassword'])->name('tenants.users.reset-password');
        Route::delete('tenants/{tenant}/users/{user}', [TenantUserController::class, 'destroy'])->name('tenants.users.destroy');
    });

/*
|--------------------------------------------------------------------------
| PANEL OWNER — cuma role owner
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('staff', StaffController::class); // kelola akun kasir
    });

/*
|--------------------------------------------------------------------------
| MENU BISNIS SEHARI-HARI — owner & kasir (tenant-scoped)
| Tiap grup dikunci lagi pakai menu:xxx biar kasir yang gak dikasih akses
| ke menu tertentu (lihat User::canAccessMenu()) gak bisa masuk walau
| role-nya sama-sama lolos role:owner,kasir.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:owner,kasir'])->group(function () {

    // Kategori
    Route::middleware('menu:categories')->group(function () {
        Route::resource('categories', CategoryController::class);
    });

    // Produk & stok
    Route::middleware('menu:products')->group(function () {
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');

        // Varian produk (nested, shallow)
        Route::resource('products.variants', ProductVariantController::class)
            ->shallow()
            ->parameters(['variants' => 'variant']);
    });

    // Bahan baku (ingredients) & penyesuaian stoknya
    Route::middleware('menu:ingredients')->group(function () {
        Route::resource('ingredients', IngredientController::class);
        Route::post('ingredients/{ingredient}/adjust-stock', [IngredientController::class, 'adjustStock'])
            ->name('ingredients.adjust-stock');
    });

    // Riwayat stok (kartu stok, immutable)
    Route::middleware('menu:stock-movements')->group(function () {
        Route::resource('stock-movements', StockMovementController::class)->only(['index', 'create', 'store', 'show']);
    });

    // Customer & piutang
    Route::middleware('menu:customers')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::get('customers-piutang', [CustomerController::class, 'piutang'])->name('customers.piutang');
        Route::post('customers/{customer}/pay-piutang', [CustomerController::class, 'payPiutang'])->name('customers.pay-piutang');
    });

    // Transaksi (kasir)
    Route::middleware('menu:transactions')->group(function () {
        Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('transactions/{transaction}/pay-piutang', [TransactionController::class, 'payPiutang'])->name('transactions.pay-piutang');
        Route::patch('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::get('transactions/{transaction}/pdf', [TransactionController::class, 'downloadPdf'])->name('transactions.pdf');
    });

    // Laporan & rekap
    Route::middleware('menu:reports')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/pdf/{type}', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('reports/export/excel/{type}', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });
});

require __DIR__.'/auth.php';