<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\OwnerWebController;
use App\Http\Controllers\Web\KasirWebController;
use App\Http\Controllers\Web\NotificationWebController;

// ─── Auth ───────────────────────────────────────────
Route::get('/',        [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthWebController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

// ─── Notifikasi (dipakai baik oleh Owner maupun Kasir) ──
Route::middleware('auth')->group(function () {
    Route::post('/notifications/read-all', [NotificationWebController::class, 'markAllRead'])->name('notifications.readAll');
    Route::get('/notifications/poll',      [NotificationWebController::class, 'poll'])->name('notifications.poll');
});

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

    Route::get('/notifications',                [NotificationWebController::class, 'history'])->name('notifications.history');
    Route::post('/notifications/{notification}/read', [NotificationWebController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}',    [NotificationWebController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/settings',            [OwnerWebController::class, 'settings'])->name('settings');
    Route::post('/settings',           [OwnerWebController::class, 'updateSettings'])->name('settings.update');

    Route::get('/discounts',                 [OwnerWebController::class, 'discounts'])->name('discounts');
    Route::post('/discounts',                [OwnerWebController::class, 'storeDiscount'])->name('discounts.store');
    Route::put('/discounts/{discount}',      [OwnerWebController::class, 'updateDiscount'])->name('discounts.update');
    Route::delete('/discounts/{discount}',   [OwnerWebController::class, 'destroyDiscount'])->name('discounts.destroy');

    Route::get('/users',                        [OwnerWebController::class, 'users'])->name('users');
    Route::post('/users',                       [OwnerWebController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}',                 [OwnerWebController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/reset-password', [OwnerWebController::class, 'resetPasswordUser'])->name('users.resetPassword');
    Route::delete('/users/{user}',              [OwnerWebController::class, 'destroyUser'])->name('users.destroy');
});

// ─── Kasir ──────────────────────────────────────────
Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/pos',           [KasirWebController::class, 'pos'])->name('pos');
    Route::post('/pos/checkout', [KasirWebController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/apply-discount', [KasirWebController::class, 'applyDiscount'])->name('pos.applyDiscount');
    Route::get('/pos/receipt/{transaction}', [KasirWebController::class, 'receipt'])->name('pos.receipt');

    Route::get('/shift',         [KasirWebController::class, 'shift'])->name('shift');
    Route::post('/shift/open',   [KasirWebController::class, 'openShift'])->name('shift.open');
    Route::post('/shift/close',  [KasirWebController::class, 'closeShift'])->name('shift.close');

    Route::get('/transactions',  [KasirWebController::class, 'transactions'])->name('transactions');

    Route::get('/notifications',                [NotificationWebController::class, 'history'])->name('notifications.history');
    Route::post('/notifications/{notification}/read', [NotificationWebController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}',    [NotificationWebController::class, 'destroy'])->name('notifications.destroy');
});
