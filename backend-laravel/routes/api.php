<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DashboardController;

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

    // Products
    Route::get('/products',              [ProductController::class, 'index']);
    Route::post('/products',             [ProductController::class, 'store']);
    Route::put('/products/{product}',    [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Stocks
    Route::get('/stocks',         [StockController::class, 'index']);
    Route::post('/stocks',        [StockController::class, 'store']);
    Route::post('/stocks/adjust', [StockController::class, 'adjust']);

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
