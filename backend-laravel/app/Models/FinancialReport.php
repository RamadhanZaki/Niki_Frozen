<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    protected $fillable = [
        'branch_id',
        'date',
        'total_revenue',
        'total_expense',
        'net_profit',
        'total_transactions',
    ];

    protected function casts(): array
    {
        return [
            'date'               => 'date',
            'total_revenue'      => 'decimal:2',
            'total_expense'      => 'decimal:2',
            'net_profit'         => 'decimal:2',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
