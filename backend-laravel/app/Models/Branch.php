<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
    ];

    // Relasi ke Users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relasi ke Products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Relasi ke Stocks
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    // Relasi ke Shifts
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    // Relasi ke Transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Relasi ke Financial Reports
    public function financialReports()
    {
        return $this->hasMany(FinancialReport::class);
    }
}
