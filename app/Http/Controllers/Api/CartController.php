<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->carts()->with('product')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $user = $request->user();
        $qty = $request->quantity ?? 1;

        // Cek apakah produk sudah ada di keranjang (karena ada unique constraint di database)
        $cart = Cart::where('user_id', $user->id)->where('product_id', $request->product_id)->first();

        if ($cart) {
            $cart->increment('quantity', $qty);
        } else {
            $cart = Cart::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'quantity' => $qty
            ]);
        }

        return response()->json(['message' => 'Ditambahkan ke keranjang', 'cart' => $cart], 201);
    }

    // Di dalam CartController.php
    public function update(Request $request, $id)
    {
        // Validasi: Pastikan quantity ada dan minimal 1
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        // Cari item di keranjang berdasarkan ID item keranjang (bukan ID produk)
        $cartItem = Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
        }

        // Update nilai quantity
        $cartItem->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jumlah item berhasil diupdate',
            'data' => $cartItem
        ], 200);
    }

    public function destroy($id)
    {
        $cartItem = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $cartItem->delete();

        return response()->json(['message' => 'Berhasil dihapus']);
    }
}
