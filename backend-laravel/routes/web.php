<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\OwnerWebController;
use App\Http\Controllers\Web\KasirWebController;

// ─── Auth ───────────────────────────────────────────
Route::get('/',        [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthWebController::class, 'login']);
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

// ─── Owner ──────────────────────────────────────────
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard',           [OwnerWebController::class, 'dashboard'])->name('dashboard');

    Route::get('/products',            [OwnerWebController::class, 'products'])->name('products');
    Route::post('/products',           [OwnerWebController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{product}',  [OwnerWebController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}',[OwnerWebController::class, 'destroyProduct'])->name('products.destroy');

    Route::get('/stocks',              [OwnerWebController::class, 'stocks'])->name('stocks');
    Route::post('/stocks/adjust',      [OwnerWebController::class, 'adjustStock'])->name('stocks.adjust');

    Route::get('/reports',             [OwnerWebController::class, 'reports'])->name('reports');

    Route::get('/branches',            [OwnerWebController::class, 'branches'])->name('branches');
    Route::post('/branches',           [OwnerWebController::class, 'storeBranch'])->name('branches.store');
    Route::put('/branches/{branch}',   [OwnerWebController::class, 'updateBranch'])->name('branches.update');
    Route::delete('/branches/{branch}',[OwnerWebController::class, 'destroyBranch'])->name('branches.destroy');

    Route::get('/shifts',              [OwnerWebController::class, 'shifts'])->name('shifts');

    Route::get('/settings',            [OwnerWebController::class, 'settings'])->name('settings');
    Route::post('/settings',           [OwnerWebController::class, 'updateSettings'])->name('settings.update');
});

// ─── Kasir ──────────────────────────────────────────
Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/pos',           [KasirWebController::class, 'pos'])->name('pos');
    Route::post('/pos/checkout', [KasirWebController::class, 'checkout'])->name('pos.checkout');

    Route::get('/shift',         [KasirWebController::class, 'shift'])->name('shift');
    Route::post('/shift/open',   [KasirWebController::class, 'openShift'])->name('shift.open');
    Route::post('/shift/close',  [KasirWebController::class, 'closeShift'])->name('shift.close');

    Route::get('/transactions',  [KasirWebController::class, 'transactions'])->name('transactions');
});