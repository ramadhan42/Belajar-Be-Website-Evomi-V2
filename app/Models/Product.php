<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id']; // Membuka semua field untuk mass-assignment kecuali ID

    public function carts() { return $this->hasMany(Cart::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function recommendedInQuizzes() { return $this->hasMany(QuizAttempt::class, 'product_id'); }
}