<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        // 1. Ambil semua item keranjang user
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }

        DB::transaction(function () use ($user, $cartItems) {
            foreach ($cartItems as $item) {
                // 2. Simpan ke tabel Orders
                Order::create([
                    'user_id' => $user->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'total_price' => $item->product->price * $item->quantity,
                    'status' => 'Selesai'
                ]);
            }

            // 3. Hapus keranjang setelah checkout berhasil
            Cart::where('user_id', $user->id)->delete();
        });

        return response()->json(['message' => 'Checkout berhasil!'], 200);
    }
}