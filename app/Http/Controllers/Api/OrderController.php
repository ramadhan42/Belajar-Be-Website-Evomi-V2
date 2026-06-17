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
        $items = $request->input('items');

        if (empty($items)) {
            return response()->json(['message' => 'Daftar pesanan kosong'], 400);
        }

        // Ambil satu waktu seragam untuk semua item dalam 1 keranjang checkout ini
        $now = now();

        DB::transaction(function () use ($user, $items, $now) {
            foreach ($items as $item) {
                Order::create([
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'status' => 'Selesai',
                    'created_at' => $now, // Paksa gunakan waktu yang sama
                    'updated_at' => $now
                ]);
            }

            // Hapus keranjang user setelah checkout berhasil
            Cart::where('user_id', $user->id)->delete();
        });

        return response()->json(['message' => 'Checkout berhasil!'], 200);
    }

    // Tambahkan di dalam OrderController.php
    public function destroy($id, Request $request)
    {
        $user = $request->user();

        // Cari pesanan berdasarkan ID dan pastikan itu milik user yang sedang login
        $order = Order::where('user_id', $user->id)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Riwayat pesanan tidak ditemukan'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Riwayat pesanan berhasil dihapus'], 200);
    }
}