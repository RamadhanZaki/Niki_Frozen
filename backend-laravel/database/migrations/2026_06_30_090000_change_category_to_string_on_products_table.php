<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom 'category' dari ENUM(5 pilihan tetap) menjadi VARCHAR(50)
     * supaya owner bisa mengetik kategori baru secara manual tanpa
     * error "Data truncated for column 'category'".
     *
     * Pakai raw SQL (bukan ->change()) karena project ini tidak meng-install
     * doctrine/dbal, jadi metode Schema::table()->change() tidak bisa dipakai.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `products` MODIFY `category` VARCHAR(50) NOT NULL DEFAULT 'Frozen'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `products` MODIFY `category` ENUM('Frozen','Snack','Dessert','Minuman','Lainnya') NOT NULL DEFAULT 'Frozen'");
    }
};
