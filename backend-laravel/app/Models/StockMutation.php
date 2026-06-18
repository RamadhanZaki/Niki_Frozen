<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [
        'product_id', 'branch_id', 'user_id',
        'type', 'quantity', 'before_stock', 'after_stock', 'note',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
