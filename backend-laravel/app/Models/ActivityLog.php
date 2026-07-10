<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false; // hanya ada created_at, tidak pernah diupdate

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'branch_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Label & warna badge untuk tiap kode aksi — dipakai di tampilan owner/
     * activity-logs.blade.php supaya jenis aksi mudah dipindai sekilas tanpa
     * baca deskripsi lengkap satu-satu. Kode aksi baru yang belum terdaftar
     * di sini tetap tampil (fallback ke label mentahnya sendiri + badge abu),
     * jadi menambah jenis aksi baru di kode TIDAK WAJIB langsung diikuti
     * update di sini.
     */
    private const ACTION_META = [
        'login'                 => ['Login', 'bg-secondary'],
        'logout'                => ['Logout', 'bg-secondary'],
        'product_created'       => ['Produk Ditambahkan', 'bg-success'],
        'product_updated'       => ['Produk Diubah', 'bg-info'],
        'product_deleted'       => ['Produk Dihapus', 'bg-danger'],
        'stock_adjusted'        => ['Stok Disesuaikan', 'bg-info'],
        'branch_created'        => ['Cabang Ditambahkan', 'bg-success'],
        'branch_updated'        => ['Cabang Diubah', 'bg-info'],
        'branch_deleted'        => ['Cabang Dihapus', 'bg-danger'],
        'discount_created'      => ['Diskon Ditambahkan', 'bg-success'],
        'discount_updated'      => ['Diskon Diubah', 'bg-info'],
        'discount_deleted'      => ['Diskon Dihapus', 'bg-danger'],
        'user_created'          => ['Kasir Ditambahkan', 'bg-success'],
        'user_updated'          => ['Kasir Diubah', 'bg-info'],
        'user_password_reset'   => ['Password Direset', 'bg-warning text-dark'],
        'user_deleted'          => ['Kasir Dihapus', 'bg-danger'],
        'settings_updated'      => ['Pengaturan Diubah', 'bg-info'],
        'shift_opened'          => ['Shift Dibuka', 'bg-success'],
        'shift_closed'          => ['Shift Ditutup', 'bg-secondary'],
        'shift_cash_difference' => ['Selisih Kas', 'bg-danger'],
    ];

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_META[$this->action][0] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    public function getActionBadgeClassAttribute(): string
    {
        return self::ACTION_META[$this->action][1] ?? 'bg-light text-dark';
    }
}
