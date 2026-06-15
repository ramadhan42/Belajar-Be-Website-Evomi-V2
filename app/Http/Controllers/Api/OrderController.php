<?php

// OrderController.php
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
        
        // Tangkap data items yang dikirim dari Next.js
        $items = $request->input('items');

        if (empty($items)) {
            return response()->json(['message' => 'Daftar pesanan kosong'], 400);
        }

        DB::transaction(function () use ($user, $items, $request) {
            foreach ($items as $item) {
                Order::create([
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    // Asumsi Frontend mengirim price yang benar, 
                    // namun di produksi nyata (production), harga sebaiknya dicek ulang ke tabel Product demi keamanan.
                    'total_price' => $item['price'] * $item['quantity'], 
                    'status' => 'Selesai' // Atau 'Pending' jika menunggu pembayaran
                ]);
            }

            // Hapus keranjang user jika checkout berhasil (opsional, sesuaikan dengan alur bisnis Anda)
            Cart::where('user_id', $user->id)->delete();
        });

        return response()->json(['message' => 'Checkout berhasil!'], 200);
    }
}