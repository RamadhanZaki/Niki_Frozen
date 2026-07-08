<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'quota',
        'used_count',
        'branch_id',
        'valid_from',
        'valid_until',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'value'        => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'valid_from'   => 'datetime',
            'valid_until'  => 'datetime',
            'is_active'    => 'boolean',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Status ringkas untuk ditampilkan di tabel Owner. Beda dari kolom
     * is_active mentah karena ikut mempertimbangkan tanggal & kuota —
     * supaya Owner tidak perlu menghitung manual apakah kode masih
     * benar-benar bisa dipakai hari ini.
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'nonaktif';
        }
        if (now()->lt($this->valid_from)) {
            return 'belum_mulai';
        }
        if (now()->gt($this->valid_until)) {
            return 'kadaluarsa';
        }
        if (!is_null($this->quota) && $this->used_count >= $this->quota) {
            return 'kuota_habis';
        }

        return 'aktif';
    }

    /**
     * Validasi lengkap sebuah kode diskon untuk subtotal & cabang tertentu.
     * Melempar RuntimeException dengan pesan yang langsung bisa ditampilkan
     * ke kasir kalau tidak valid.
     *
     * Dipakai di DUA tempat dengan parameter $lock berbeda:
     * - Preview (KasirWebController::applyDiscount): $lock=false, cuma baca,
     *   tidak perlu kunci baris karena tidak ada perubahan data.
     * - Checkout sungguhan (KasirWebController::checkout): $lock=true, DI
     *   DALAM DB::transaction, supaya baris kode diskon dikunci sebelum
     *   used_count ditambah — mencegah race condition kalau kuota tersisa
     *   1 dan dua kasir memakainya nyaris bersamaan (pola yang sama persis
     *   dengan lockForUpdate() pada Stock di checkout()).
     */
    public static function validateForCheckout(string $code, float $subtotal, ?int $branchId, bool $lock = false): self
    {
        $query = self::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))]);

        if ($lock) {
            $query->lockForUpdate();
        }

        $discount = $query->first();

        if (!$discount) {
            throw new \RuntimeException('Kode diskon tidak ditemukan.');
        }
        if (!$discount->is_active) {
            throw new \RuntimeException('Kode diskon tidak aktif.');
        }
        if (now()->lt($discount->valid_from)) {
            throw new \RuntimeException('Kode diskon belum berlaku.');
        }
        if (now()->gt($discount->valid_until)) {
            throw new \RuntimeException('Kode diskon sudah kadaluarsa.');
        }
        if (!is_null($discount->branch_id) && $discount->branch_id !== $branchId) {
            throw new \RuntimeException('Kode diskon tidak berlaku untuk cabang ini.');
        }
        if ($subtotal < (float) $discount->min_purchase) {
            throw new \RuntimeException(
                'Minimal belanja untuk kode ini Rp'.number_format((float) $discount->min_purchase, 0, ',', '.').'.'
            );
        }
        if (!is_null($discount->quota) && $discount->used_count >= $discount->quota) {
            throw new \RuntimeException('Kuota kode diskon sudah habis.');
        }

        return $discount;
    }

    /**
     * Hitung nominal potongan untuk subtotal tertentu. Untuk type=percentage,
     * dibatasi oleh max_discount (kalau diisi) supaya tidak jebol untuk
     * belanja besar. Hasil akhir juga tidak pernah melebihi subtotal itu
     * sendiri (mencegah subtotal setelah diskon jadi negatif).
     */
    public function calculateDiscountAmount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            $amount = $subtotal * ((float) $this->value / 100);
            if (!is_null($this->max_discount)) {
                $amount = min($amount, (float) $this->max_discount);
            }
        } else {
            $amount = (float) $this->value;
        }

        return min($amount, $subtotal);
    }
}
