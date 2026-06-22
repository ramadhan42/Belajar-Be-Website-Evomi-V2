<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Tambahkan metode_pembayaran di sini
    protected $fillable = ['id', 'user_id', 'product_id', 'quantity', 'total_price', 'status', 'metode_pembayaran'];

    public $incrementing = false;      // Matikan auto-increment
    protected $keyType = 'string';

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