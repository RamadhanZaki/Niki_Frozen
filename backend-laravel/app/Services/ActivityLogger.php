<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Helper terpusat untuk mencatat jejak audit (activity log). Dipanggil satu
 * baris dari controller manapun setiap kali ada aksi penting yang perlu
 * tercatat — supaya format pencatatan (siapa, kapan, dari IP mana) konsisten
 * di seluruh aplikasi dan tidak perlu diulang-ulang manual di tiap tempat.
 *
 * Sengaja dibuat sebagai static helper (bukan di-inject lewat constructor)
 * karena dipakai di banyak controller berbeda yang sudah punya banyak
 * dependency lain — menambah constructor injection di semuanya cuma untuk
 * satu helper logging akan lebih mengganggu daripada membantu.
 */
class ActivityLogger
{
    /**
     * @param  string      $action       Kode aksi terstandar, mis. 'product_created'.
     *                                   Lihat ActivityLog::ACTION_META untuk daftar yang
     *                                   sudah punya label & warna badge di tampilan.
     * @param  string      $description  Ringkasan siap-tampil dalam bahasa manusia,
     *                                   mis. "Menghapus produk Nugget Ayam 500gr".
     * @param  Model|null  $subject      Record yang dipengaruhi (Product, Branch, dst).
     *                                   Boleh null untuk aksi yang tidak menunjuk ke
     *                                   record tertentu (mis. login/logout).
     * @param  array|null  $properties   Detail tambahan terstruktur (nilai lama/baru,
     *                                   nominal, dsb) untuk audit yang lebih dalam.
     * @param  int|null    $branchId     Cabang tempat aksi terjadi. Kalau tidak diisi,
     *                                   dipakai branch_id user yang sedang login (kalau
     *                                   ada) — supaya pemanggil tidak wajib selalu isi ini
     *                                   secara eksplisit padahal sudah tersirat dari user.
     */
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?int $branchId = null
    ): void {
        try {
            ActivityLog::create([
                'user_id'      => Auth::id(),
                'branch_id'    => $branchId ?? Auth::user()?->branch_id,
                'action'       => $action,
                'subject_type' => $subject ? $subject->getMorphClass() : null,
                'subject_id'   => $subject?->getKey(),
                'description'  => $description,
                'properties'   => $properties,
                'ip_address'   => Request::ip(),
            ]);
        } catch (\Throwable $e) {
            // Gagal mencatat log TIDAK BOLEH pernah menggagalkan aksi utama
            // (mis. checkout tetap harus sukses walau baris audit gagal
            // tersimpan karena sebab apa pun). Cukup diamkan di sini — kalau
            // butuh observability lebih jauh, ini titik yang tepat untuk
            // ditambah Log::error() ke storage/logs/laravel.log nanti.
        }
    }
}
