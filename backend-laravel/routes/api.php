<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Test endpoint
Route::get('/test', function () {
    return response()->json(['message' => 'Backend Laravel berjalan!']);
});

// Auth routes (public)
Route::post('/login', [AuthController::class, 'login']);

// Auth routes (protected - butuh token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
