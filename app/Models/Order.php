<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'product_id', 'quantity', 'total_price', 'status'];

    // Relasi: Satu pesanan milik satu produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi: Satu pesanan milik satu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}