<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'difference',
        'total_sales',
        'total_cash_sales',
        'total_qris_sales',
        'total_transactions',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_cash'      => 'decimal:2',
            'closing_cash'      => 'decimal:2',
            'expected_cash'     => 'decimal:2',
            'difference'        => 'decimal:2',
            'total_sales'       => 'decimal:2',
            'total_cash_sales'  => 'decimal:2',
            'total_qris_sales'  => 'decimal:2',
            'opened_at'         => 'datetime',
            'closed_at'         => 'datetime',
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }
}
