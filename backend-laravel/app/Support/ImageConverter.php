<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageConverter
{
    /**
     * Kualitas WebP default. 80 adalah titik seimbang yang umum dipakai:
     * secara visual nyaris tidak beda dari kualitas 100, tapi ukuran file
     * jauh lebih kecil. Naikkan ke ~90 kalau butuh detail gambar produk yang
     * sangat presisi, atau turunkan ke ~60-70 kalau prioritasnya kecepatan
     * loading halaman POS (banyak gambar produk dimuat sekaligus).
     */
    private const DEFAULT_QUALITY = 80;

    /**
     * Konversi file gambar yang diupload (JPG/PNG/GIF/WebP) menjadi WebP dan
     * simpan ke disk 'public'. Mengembalikan path relatif hasil simpan
     * (format yang sama seperti hasil UploadedFile::store()), supaya bisa
     * langsung dipakai menggantikan pemanggilan store() yang lama.
     *
     * WebP dipilih karena ukurannya biasanya 25-35% lebih kecil dari JPEG
     * pada kualitas visual yang setara — penting untuk halaman POS yang
     * memuat banyak gambar produk sekaligus, apalagi kalau diakses dari
     * jaringan toko yang koneksinya pas-pasan.
     *
     * @throws \RuntimeException kalau ekstensi GD tidak aktif di server,
     *         format gambar tidak didukung, atau file gagal dibaca/ditulis.
     */
    public static function toWebp(UploadedFile $file, string $directory, int $quality = self::DEFAULT_QUALITY): string
    {
        if (!function_exists('imagewebp')) {
            // Bukan error yang bisa diperbaiki user — ini masalah konfigurasi
            // server (ekstensi GD belum aktif di php.ini / build server).
            throw new \RuntimeException(
                'Server belum mendukung konversi gambar ke WebP (ekstensi PHP GD tidak aktif). Hubungi administrator.'
            );
        }

        $sourcePath = $file->getRealPath();
        $mime = $file->getMimeType();

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/gif'  => @imagecreatefromgif($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => throw new \RuntimeException("Format gambar ({$mime}) tidak didukung untuk konversi WebP."),
        };

        if (!$image) {
            throw new \RuntimeException('Gagal membaca file gambar yang diupload — kemungkinan file rusak.');
        }

        // Pertahankan transparansi (penting untuk PNG/GIF) supaya area
        // transparan tidak berubah jadi hitam solid setelah dikonversi.
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $filename = trim($directory, '/').'/'.Str::random(32).'.webp';
        $disk = Storage::disk('public');

        // Storage::path() tidak otomatis membuat folder tujuan kalau belum ada.
        $fullPath = $disk->path($filename);
        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $saved = imagewebp($image, $fullPath, $quality);
        imagedestroy($image);

        if (!$saved) {
            throw new \RuntimeException('Gagal menyimpan hasil konversi gambar ke WebP.');
        }

        return $filename;
    }
}
