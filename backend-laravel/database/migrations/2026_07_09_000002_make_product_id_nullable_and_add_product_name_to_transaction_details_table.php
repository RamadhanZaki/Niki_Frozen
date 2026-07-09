<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sebelumnya product_id di transaction_details pakai cascadeOnDelete().
     * Efeknya: begitu Owner hapus produk, SEMUA baris transaction_details yang
     * pernah pakai produk itu ikut terhapus permanen — bukan cuma relasinya
     * jadi null. Ini membuat struk & laporan lama kehilangan baris item diam-
     * diam, padahal view-nya (receipt.blade.php, transactions.blade.php,
     * owner/reports.blade.php) sudah lebih dulu ditulis mengantisipasi kondisi
     * "produk sudah dihapus" lewat fallback `?? 'Produk dihapus'` — kode itu
     * jadi tidak pernah kepakai karena barisnya keburu lenyap sebelum sempat
     * ditampilkan.
     *
     * Perbaikan di sini menyamakan pola dengan discount_code_id di tabel
     * transactions (lihat 2026_07_09_000001_add_discount_to_transactions_table):
     * nullOnDelete(), bukan cascadeOnDelete(). Riwayat transaksi jadi permanen
     * walau produknya belakangan dihapus.
     *
     * Ditambah kolom product_name sebagai SNAPSHOT nama produk saat transaksi
     * terjadi — pola yang sama persis dengan price_at_sale/subtotal yang sudah
     * ada di tabel ini. Ini lebih akurat daripada cuma fallback "Produk
     * dihapus": struk & laporan tetap menampilkan nama ASLI produk saat itu,
     * bahkan kalau produknya belakangan di-rename lewat updateProduct(), bukan
     * cuma saat dihapus.
     */
    public function up(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // Ubah kolom jadi nullable lewat raw SQL — proyek ini tidak
        // menginstal doctrine/dbal (dependency yang biasanya dibutuhkan
        // Schema::table(...)->change() di Laravel), jadi dihindari supaya
        // migrasi tetap jalan tanpa menambah dependency baru.
        DB::statement('ALTER TABLE transaction_details MODIFY product_id BIGINT UNSIGNED NULL');

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->string('product_name', 150)->nullable()->after('product_id');
        });

        // ── Backfill ──────────────────────────────────────────────────
        // Isi product_name untuk baris LAMA yang produknya masih ada saat
        // migrasi ini dijalankan. Baris lama yang produknya SUDAH terlanjur
        // dihapus sebelum migrasi ini (lewat cascadeOnDelete() versi lama)
        // otomatis sudah ikut hilang duluan, jadi tidak ada yang bisa
        // di-backfill untuk kasus itu — product_name-nya akan tetap null,
        // dan tampilan tetap jatuh ke fallback 'Produk dihapus' seperti biasa.
        DB::statement(
            'UPDATE transaction_details td
             JOIN products p ON p.id = td.product_id
             SET td.product_name = p.name
             WHERE td.product_name IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_name');
        });

        DB::statement('ALTER TABLE transaction_details MODIFY product_id BIGINT UNSIGNED NOT NULL');

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
