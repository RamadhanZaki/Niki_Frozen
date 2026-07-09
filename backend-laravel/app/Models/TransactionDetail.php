<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'qty',
        'price_at_sale',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'price_at_sale' => 'decimal:2',
            'subtotal'      => 'decimal:2',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Nama produk yang ditampilkan di struk/riwayat/laporan. Prioritas:
     * 1. Snapshot product_name (nama ASLI saat transaksi terjadi — akurat
     *    walau produk belakangan di-rename atau dihapus).
     * 2. Nama produk LIVE lewat relasi — jaring pengaman untuk baris lama
     *    yang dibuat sebelum kolom product_name ada dan belum sempat
     *    ter-backfill, tapi produknya kebetulan masih ada.
     * 3. 'Produk dihapus' — kalau dua-duanya tidak tersedia.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->product_name ?? $this->product?->name ?? 'Produk dihapus';
    }
}
