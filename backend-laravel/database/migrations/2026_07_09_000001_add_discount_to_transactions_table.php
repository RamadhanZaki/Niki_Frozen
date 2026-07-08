<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Dicatat untuk audit/laporan — kode diskon mana (kalau ada) yang
            // dipakai di transaksi ini. nullOnDelete supaya kode diskon lama
            // masih bisa dihapus tanpa menghapus riwayat transaksi.
            $table->foreignId('discount_code_id')->nullable()->after('shift_id')
                ->constrained('discount_codes')->nullOnDelete();

            // 'subtotal' TETAP berarti subtotal keranjang SEBELUM diskon (harga asli),
            // supaya laporan/struk bisa menampilkan "Subtotal → Diskon → Pajak → Total"
            // dengan jelas. Pajak dihitung dari (subtotal - discount_amount).
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discount_code_id');
            $table->dropColumn('discount_amount');
        });
    }
};
