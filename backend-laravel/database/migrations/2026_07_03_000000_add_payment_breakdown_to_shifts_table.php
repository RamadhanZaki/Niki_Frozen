<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('shifts', function (Blueprint $table) {
            // Dipisah dari total_sales supaya rekonsiliasi kas fisik (closing_cash)
            // hanya membandingkan dengan uang yang benar-benar masuk laci (cash),
            // bukan pembayaran QRIS yang masuk ke rekening/akun QRIS terpisah.
            $table->decimal('total_cash_sales', 12, 2)->default(0)->after('total_sales');
            $table->decimal('total_qris_sales', 12, 2)->default(0)->after('total_cash_sales');
        });
    }
    public function down(): void {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['total_cash_sales', 'total_qris_sales']);
        });
    }
};
