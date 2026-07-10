<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel jejak audit (audit trail) untuk seluruh aksi penting yang
     * dilakukan Owner maupun Kasir — login/logout, CRUD produk/cabang/user/
     * diskon, penyesuaian stok, buka/tutup shift, dan perubahan pengaturan
     * toko. Tujuannya supaya kalau ada perselisihan atau kejanggalan data
     * (mis. harga produk tiba-tiba berubah, stok berkurang tanpa transaksi,
     * kode diskon yang tidak seharusnya aktif), Owner bisa menelusuri SIAPA
     * yang melakukan apa dan KAPAN — bukan cuma tahu kondisi "sekarang".
     *
     * Didesain independen dari StockMutation (yang sudah ada sebelumnya):
     * StockMutation tetap jadi sumber detail teknis mutasi stok (before/after
     * quantity), sedangkan activity_logs jadi satu linimasa TERPUSAT yang
     * merangkum semua jenis aksi (termasuk stok) supaya Owner tidak perlu
     * buka banyak tabel berbeda untuk menyusun kronologi kejadian.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Nullable: kalau suatu saat ada aksi sistem otomatis (bukan
            // dipicu user login manapun), tetap bisa tercatat tanpa error FK.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Cabang tempat aksi terjadi (bukan selalu cabang si user — mis.
            // Owner mengubah produk cabang lain). Nullable untuk aksi yang
            // tidak terikat cabang tertentu (mis. login, ubah pengaturan toko).
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Kode aksi terstandar (mis. 'login', 'product_created',
            // 'stock_adjusted', 'shift_closed') — dipakai untuk filter &
            // menentukan warna badge di tampilan, BUKAN untuk ditampilkan
            // mentah-mentah ke Owner (lihat accessor actionLabel di model).
            $table->string('action', 60);

            // Morph ke record yang dipengaruhi (Product, Branch, User, dst).
            // Nullable karena beberapa aksi (login/logout) tidak menunjuk ke
            // record model tertentu.
            $table->nullableMorphs('subject');

            // Ringkasan yang sudah dalam bahasa manusia & siap tampil apa
            // adanya di tabel riwayat, mis. "Menghapus produk Nugget Ayam 500gr".
            // Disimpan sebagai teks jadi (bukan dirakit ulang saat ditampilkan)
            // supaya riwayat lama tetap terbaca persis seperti kejadian aslinya
            // walau nama/atribut record terkait berubah belakangan.
            $table->string('description', 500);

            // Detail tambahan terstruktur (nilai lama/baru, nominal, dsb) untuk
            // kebutuhan audit yang lebih dalam tanpa perlu menambah kolom baru
            // tiap kali ada jenis aksi baru.
            $table->json('properties')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Index untuk pola filter yang paling umum dipakai di halaman
            // Riwayat Aktivitas: per user, per aksi, dan urut waktu.
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
