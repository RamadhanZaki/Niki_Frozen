<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 'total' TETAP berarti nominal akhir yang dibayar customer
            // (subtotal + pajak) — supaya kode lain yang sudah memakai kolom
            // 'total' sebagai omzet (laporan, akumulasi shift, financial_reports)
            // tidak perlu diubah. 'subtotal' & 'tax_amount' cuma dipakai untuk
            // rincian breakdown di struk dan (nanti) laporan.
            $table->decimal('subtotal', 12, 2)->default(0)->after('shift_id');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_amount']);
        });
    }
};
