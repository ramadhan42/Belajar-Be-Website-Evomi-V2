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
    /**
     * READ: Mengambil detail satu pesanan berdasarkan ID untuk user yang login
     */
    public function show($id, Request $request)
    {
        $user = $request->user();

        $order = Order::with('product')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $order
        ], 200);
    }

    /**
     * READ ALL: Mengambil semua pesanan dari semua user (Untuk Admin)
     */
    public function getAllOrders()
    {
        // Memuat data produk dan user yang melakukan order
        $orders = Order::with(['product', 'user'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Semua data pesanan berhasil diambil.',
            'data' => $orders
        ], 200);
    }

    /**
     * DASHBOARD STATS: Mengambil ringkasan total pendapatan termasuk ongkir (Untuk Admin)
     */
    public function getTotalRevenue()
    {
        // 1. Hitung total harga produk asli dari semua order
        $totalProductPrice = Order::sum('total_price');

        // 2. Hitung total item/quantity yang terjual dari semua order
        $totalQuantitySold = Order::sum('quantity');

        // 3. Hitung total ongkir (Rp 1.000 per produk/quantity)
        $shippingCostPerItem = 1000;
        $totalShippingRevenue = $totalQuantitySold * $shippingCostPerItem;

        // 4. Akumulasikan total pendapatan bersih + ongkir
        $totalRevenueWithShipping = $totalProductPrice + $totalShippingRevenue;

        // 5. Hitung jumlah transaksi/invoice secara keseluruhan
        $totalOrdersCount = Order::count();

        return response()->json([
            'success' => true,
            'message' => 'Data ringkasan pendapatan admin (termasuk ongkir) berhasil dimuat.',
            'data' => [
                'total_revenue' => (int) $totalRevenueWithShipping,
                'total_orders_count' => $totalOrdersCount,
                'total_items_sold' => (int) $totalQuantitySold,
                'total_shipping_cost' => (int) $totalShippingRevenue,
                'currency' => 'IDR'
            ]
        ], 200);
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $items = $request->input('items');
        $invoiceId = $request->input('invoice_id');

        // Mengambil metode pembayaran dari request (menggunakan default string kosong jika tidak ada)
        $metodePembayaran = $request->input('payment_method', $request->input('metode_pembayaran', ''));

        if (empty($items)) {
            return response()->json(['message' => 'Daftar pesanan kosong'], 400);
        }

        if (!$invoiceId) {
            return response()->json(['message' => 'Invoice ID tidak valid'], 400);
        }

        $now = now();

        DB::transaction(function () use ($user, $items, $invoiceId, $now, $metodePembayaran) {
            foreach ($items as $index => $item) {
                $orderId = count($items) > 1 ? "{$invoiceId}-" . ($index + 1) : $invoiceId;

                Order::create([
                    'id' => $orderId,
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['price'] * $item['quantity'],
                    'status' => 'menunggu_konfirmasi',
                    'metode_pembayaran' => $metodePembayaran, // <-- Simpan metode pembayaran di sini
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
     * Skenario Admin / Postman: Memperbarui status dan metode pembayaran pesanan secara spesifik
     */
    public function updateStatus($id, Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(['menunggu_konfirmasi', 'pengemasan', 'dalam_perjalanan', 'diterima'])
            ],
            // Validasi opsional untuk update metode pembayaran
            'metode_pembayaran' => 'sometimes|string|nullable'
        ]);

        // 2. Cari data
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // 3. Update status
        $order->status = $request->status;

        // 4. Update metode pembayaran jika disisipkan pada payload request
        if ($request->has('metode_pembayaran')) {
            $order->metode_pembayaran = $request->metode_pembayaran;
        }

        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Data pesanan berhasil diperbarui.',
            'data' => $order
        ], 200);
    }
}