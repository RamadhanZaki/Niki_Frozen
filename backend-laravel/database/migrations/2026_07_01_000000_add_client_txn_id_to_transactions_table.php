<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // ID unik yang dibuat di sisi kasir (browser) saat transaksi dibuat,
            // dipakai supaya transaksi offline yang disinkronkan tidak tersimpan dobel
            // walaupun request sync dikirim ulang.
            $table->string('client_txn_id', 64)->nullable()->unique()->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('client_txn_id');
        });
    }
};
