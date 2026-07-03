<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Selisih pembulatan kembalian tunai. Positif = total dibulatkan NAIK
            // (customer bayar sedikit lebih banyak), negatif = dibulatkan TURUN.
            // Hanya berlaku untuk payment_method = 'cash'; transaksi QRIS selalu 0
            // karena nominal digital tidak perlu dibulatkan.
            // 'total' TETAP nominal akhir yang benar-benar dibayar (SUDAH termasuk
            // pembulatan ini), supaya seluruh kode lain yang memakai kolom 'total'
            // sebagai omzet (shift, financial_reports, laporan) tidak perlu berubah.
            $table->decimal('rounding_amount', 12, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('rounding_amount');
        });
    }
};
