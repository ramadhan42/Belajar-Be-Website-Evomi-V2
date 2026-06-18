<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();
        $items = $request->input('items');

        if (empty($items)) {
            return response()->json(['message' => 'Daftar pesanan kosong'], 400);
        }

        $now = now();

        DB::transaction(function () use ($user, $items, $now) {
            foreach ($items as $item) {
                Order::create([
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'status' => 'menunggu_konfirmasi', 
                    'created_at' => $now, 
                    'updated_at' => $now
                ]);
            }

            Cart::where('user_id', $user->id)->delete();
        });

        return response()->json(['message' => 'Checkout berhasil!'], 200);
    }

    public function confirmReceipt($id, Request $request)
    {
        $order = Order::where('user_id', $request->user()->id)->where('id', $id)->firstOrFail();
        $order->update(['status' => 'selesai']);

        return response()->json(['message' => 'Pesanan telah dikonfirmasi diterima dan selesai.']);
    }

    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['message' => 'Riwayat pesanan tidak ditemukan'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Riwayat pesanan berhasil dihapus'], 200);
    }

    /**
     * Skenario Admin / Postman: Memperbarui status pesanan secara spesifik
     */
    public function updateStatus($id, Request $request)
    {
        // 1. Validasi input status agar hanya menerima status yang diizinkan oleh sistem
        $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(['menunggu_konfirmasi', 'pengemasan', 'dalam_perjalanan', 'diterima'])
            ]
        ]);

        // 2. Cari data pesanan berdasarkan ID pesanan (Global / Tanpa scope user karena ini aksi simulasi admin)
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // 3. Update status pesanan tersebut
        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui menjadi: ' . $request->status,
            'data' => $order
        ], 200);
    }
}