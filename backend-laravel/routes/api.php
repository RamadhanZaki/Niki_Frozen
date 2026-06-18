<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\CashierShiftController;
use App\Http\Controllers\TransactionController;

Route::get('/test', fn() => response()->json(['message' => 'Backend Laravel berjalan!']));

// Public
Route::post('/login', [AuthController::class, 'login']);

// Protected
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Users
    Route::get('/users',                          [UserController::class, 'index']);
    Route::post('/users',                         [UserController::class, 'store']);
    Route::get('/users/{user}',                   [UserController::class, 'show']);
    Route::put('/users/{user}',                   [UserController::class, 'update']);
    Route::delete('/users/{user}',                [UserController::class, 'destroy']);
    Route::post('/users/{user}/reset-password',   [UserController::class, 'resetPassword']);

    // Dashboard (owner only)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Reports (owner only)
    Route::get('/reports', [ReportController::class, 'index']);

    // Branches (owner only)
    Route::get('/branches',              [BranchController::class, 'index']);
    Route::post('/branches',             [BranchController::class, 'store']);
    Route::put('/branches/{branch}',     [BranchController::class, 'update']);
    Route::delete('/branches/{branch}',  [BranchController::class, 'destroy']);

    // Shifts (owner only - view)
    Route::get('/shifts', [ShiftController::class, 'index']);

    // Products
    Route::get('/products',              [ProductController::class, 'index']);
    Route::post('/products',             [ProductController::class, 'store']);
    Route::put('/products/{product}',    [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Stocks
    Route::get('/stocks',         [StockController::class, 'index']);
    Route::post('/stocks',        [StockController::class, 'store']);
    Route::post('/stocks/adjust', [StockController::class, 'adjust']);

    // Kasir — Shift
    Route::get('/cashier/shift/active',  [CashierShiftController::class, 'active']);
    Route::post('/cashier/shift/open',   [CashierShiftController::class, 'open']);
    Route::post('/cashier/shift/close',  [CashierShiftController::class, 'close']);
    Route::get('/cashier/shift/history', [CashierShiftController::class, 'history']);

    // Kasir — Transaksi
    Route::get('/cashier/transactions',        [TransactionController::class, 'index']);
    Route::post('/cashier/transactions',       [TransactionController::class, 'store']);
    Route::post('/cashier/transactions/sync',  [TransactionController::class, 'sync']);

    // Temporary: fix produk yang tidak punya record stocks
    Route::get('/fix-stocks', function () {
        $products = \App\Models\Product::whereDoesntHave('stock')->get();
        foreach ($products as $p) {
            \App\Models\Stock::create([
                'product_id' => $p->id,
                'branch_id'  => $p->branch_id,
                'quantity'   => 0,
                'min_stock'  => 10,
                'updated_at' => now(),
            ]);
        }
        return response()->json(['fixed' => $products->count() . ' produk diperbaiki']);
    });
});