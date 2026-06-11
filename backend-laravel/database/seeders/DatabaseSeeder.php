<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Branches
        DB::table('branches')->insert([
            ['name' => 'Cabang Utama', 'address' => 'Jl. Utama No. 1, Yogyakarta', 'phone' => '081234567890', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cabang Kedua', 'address' => 'Jl. Kedua No. 2, Yogyakarta', 'phone' => '081234567891', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Users
        DB::table('users')->insert([
            ['name' => 'Owner Nicky Frozen', 'email' => 'owner@nicksfrozen.com', 'password' => Hash::make('password123'), 'role' => 'owner', 'branch_id' => null, 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siti Aisyah', 'email' => 'siti@nicksfrozen.com', 'password' => Hash::make('password123'), 'role' => 'kasir', 'branch_id' => 1, 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Budi Santoso', 'email' => 'budi@nicksfrozen.com', 'password' => Hash::make('password123'), 'role' => 'kasir', 'branch_id' => 2, 'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@nicksfrozen.com', 'password' => Hash::make('password123'), 'role' => 'kasir', 'branch_id' => 1, 'status' => 'nonaktif', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Products
        $products = [
            ['name' => 'Nugget Ayam', 'category' => 'Frozen', 'price' => 35000, 'expired_date' => '2025-12-31', 'branch_id' => 1],
            ['name' => 'Sosis Solo', 'category' => 'Frozen', 'price' => 28000, 'expired_date' => '2025-11-30', 'branch_id' => 1],
            ['name' => 'Roti Bakar', 'category' => 'Snack', 'price' => 15000, 'expired_date' => '2025-10-15', 'branch_id' => 1],
            ['name' => 'Kentang Goreng', 'category' => 'Frozen', 'price' => 20000, 'expired_date' => '2025-12-20', 'branch_id' => 1],
            ['name' => 'Es Krim', 'category' => 'Dessert', 'price' => 12000, 'expired_date' => '2026-01-15', 'branch_id' => 1],
            ['name' => 'Pizza Frozen', 'category' => 'Frozen', 'price' => 55000, 'expired_date' => '2025-12-31', 'branch_id' => 1],
            ['name' => 'Dimsum', 'category' => 'Frozen', 'price' => 25000, 'expired_date' => '2025-12-10', 'branch_id' => 1],
            ['name' => 'Cireng', 'category' => 'Snack', 'price' => 10000, 'expired_date' => '2026-01-01', 'branch_id' => 1],
        ];
        foreach ($products as $p) {
            DB::table('products')->insert(array_merge($p, ['created_at' => now(), 'updated_at' => now()]));
        }

        // Stocks
        $stocks = [
            [1, 1, 45], [2, 1, 8], [3, 1, 3], [4, 1, 28],
            [5, 1, 52], [6, 1, 0], [7, 1, 15], [8, 1, 40],
        ];
        foreach ($stocks as [$pid, $bid, $qty]) {
            DB::table('stocks')->insert(['product_id' => $pid, 'branch_id' => $bid, 'quantity' => $qty, 'min_stock' => 10, 'updated_at' => now()]);
        }
    }
}
