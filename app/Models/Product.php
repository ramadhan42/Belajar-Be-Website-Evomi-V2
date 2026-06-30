<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Menambahkan field virtual URL secara otomatis ke dalam response JSON
    protected $appends = [
        'image_1_url', 
        'image_2_url', 
        'image_3_url', 
        'image_4_url', 
        'image_produk_belanja_url'
    ];

    // Accessor untuk Image 1
    public function getImage1UrlAttribute()
    {
        return $this->image_1 ? Storage::disk(config('filesystems.default'))->url($this->image_1) : null;
    }

    // Accessor untuk Image 2
    public function getImage2UrlAttribute()
    {
        return $this->image_2 ? Storage::disk(config('filesystems.default'))->url($this->image_2) : null;
    }

    // Accessor untuk Image 3
    public function getImage3UrlAttribute()
    {
        return $this->image_3 ? Storage::disk(config('filesystems.default'))->url($this->image_3) : null;
    }

    // Accessor untuk Image 4
    public function getImage4UrlAttribute()
    {
        return $this->image_4 ? Storage::disk(config('filesystems.default'))->url($this->image_4) : null;
    }

    // Accessor untuk Image Produk Belanja
    public function getImageProdukBelanjaUrlAttribute()
    {
        return $this->image_produk_belanja ? Storage::disk(config('filesystems.default'))->url($this->image_produk_belanja) : null;
    }
}