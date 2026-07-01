<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCounter extends Model
{
    protected $fillable = [
        'counter_date',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'counter_date' => 'date',
        ];
    }
}