<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // File: app/Models/User.php
    protected $fillable = [
        'name',
        'email',
        'password',
        'nama_lengkap',
        'alamat_lengkap',
        'phone',           // Tambahkan ini
        'avatar_profile',  // Tambahkan ini
    ];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
    public function shoppingNeeds()
    {
        return $this->hasMany(ShoppingNeed::class);
    }
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}