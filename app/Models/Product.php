<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'price', 
        'personality_type', 'top_note', 'middle_note', 'base_note',
        'image_1', 'image_2', 'image_3', 'image_4', 'image_produk_belanja', // <--- TAMBAHKAN DI SINI
        'bottle_size', 'perfume_type', 'gender',
        'quantity', 'stock_status'
    ];

    // Memastikan tipe data response JSON sesuai
    protected $casts = [
        'price' => 'decimal:2',
        'bottle_size' => 'integer',
        'quantity' => 'integer',
    ];

    public function carts() { return $this->hasMany(Cart::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function recommendedInQuizzes() { return $this->hasMany(QuizAttempt::class, 'product_id'); }
}