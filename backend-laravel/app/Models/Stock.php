<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    public $timestamps = false; // hanya ada updated_at

    protected $fillable = [
        'product_id',
        'branch_id',
        'quantity',
        'min_stock',
    ];

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = null;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Cek apakah stok di bawah minimum
    public function isLow(): bool
    {
        return $this->quantity <= $this->min_stock;
    }
}
