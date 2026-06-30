<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category',
        'image',
        'price',
        'expired_date',
        'branch_id',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'expired_date' => 'date',
        ];
    }

    // URL gambar produk (placeholder kalau belum ada)
    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::url($this->image);
        }
        return asset('images/no-product.svg');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}