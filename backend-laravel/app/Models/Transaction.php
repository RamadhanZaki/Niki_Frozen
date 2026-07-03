<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_number',
        'client_txn_id',
        'user_id',
        'branch_id',
        'shift_id',
        'subtotal',
        'tax_amount',
        'rounding_amount',
        'total',
        'payment',
        'payment_method',
        'change_amount',
        'status',
        'sync_status',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'rounding_amount' => 'decimal:2',
            'total'           => 'decimal:2',
            'payment'       => 'decimal:2',
            'change_amount' => 'decimal:2',
            'synced_at'     => 'datetime',
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

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
