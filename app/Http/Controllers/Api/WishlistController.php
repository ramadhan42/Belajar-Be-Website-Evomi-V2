<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // Mengambil data wishlist milik user yang sedang login
    public function index(Request $request)
    {
        return Wishlist::where('user_id', $request->user()->id)
            ->with('product') // Memuat data produk terkait
            ->get();
    }

    /**
     * READ ALL: Mengambil semua data wishlist dari semua user (Untuk Admin)
     */
    public function getAllWishlists()
    {
        // Memuat data produk dan user pemilik wishlist
        $wishlists = Wishlist::with(['product', 'user'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Semua data wishlist berhasil diambil.',
            'data' => $wishlists
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Cek apakah sudah ada di wishlist agar tidak duplikat
        $exists = Wishlist::where('user_id', $request->user()->id)
                          ->where('product_id', $request->product_id)
                          ->first();

        if ($exists) {
            return response()->json(['message' => 'Produk sudah ada di wishlist'], 400);
        }

        // Simpan ke database
        $wishlist = Wishlist::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['message' => 'Berhasil ditambahkan ke wishlist', 'data' => $wishlist], 201);
    }

    // Menghapus dari wishlist
    public function destroy($id, Request $request)
    {
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
            
        $wishlist->delete();
        return response()->json(['message' => 'Berhasil dihapus dari wishlist']);
    }
}