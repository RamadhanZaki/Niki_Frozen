<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Product;
use App\Models\Stock;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dipindah dari route API (GET /api/fix-stocks) yang sebelumnya bisa diakses
// siapa saja yang login tanpa cek role. Jalankan manual via:
//   php artisan stocks:fix
Artisan::command('stocks:fix', function () {
    $products = Product::whereDoesntHave('stock')->get();

    foreach ($products as $p) {
        Stock::create([
            'product_id' => $p->id,
            'branch_id'  => $p->branch_id,
            'quantity'   => 0,
            'min_stock'  => 10,
            'updated_at' => now(),
        ]);
    }

    $this->info($products->count() . ' produk diperbaiki (record stok dibuat).');
})->purpose('Buat record stok untuk produk yang belum punya record stocks');