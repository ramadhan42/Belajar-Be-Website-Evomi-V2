<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // READ: Ambil semua produk
    public function index()
    {
        $products = Product::all();
        return response()->json(['success' => true, 'data' => $products], 200);
    }

    // READ: Ambil detail 1 produk
    public function show($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $product], 200);
    }

    // CREATE: Tambah produk baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'color' => 'nullable|string|max:50', // <--- TAMBAHKAN VALIDASI COLOR DI SINI
            'price' => 'required|numeric',
            'personality_type' => 'nullable|in:prestige,peaceful_calm,rebel_brave,sweet_shy',
            'top_note' => 'nullable|string|max:255',
            'middle_note' => 'nullable|string|max:255',
            'base_note' => 'nullable|string|max:255',
            'image_1' => 'required|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_4' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_produk_belanja' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'bottle_size' => 'required|integer',
            'perfume_type' => 'required|string|max:255',
            'gender' => 'required|in:unisex,male,female',
            'quantity' => 'integer',
            'stock_status' => 'in:tersedia,minim,habis',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image_1', 'image_2', 'image_3', 'image_4', 'image_produk_belanja']);

        // Upload gambar ke storage/app/public/products
        $imageFields = ['image_1', 'image_2', 'image_3', 'image_4', 'image_produk_belanja'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('products', 'public');
            }
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true, 
            'message' => 'Produk berhasil ditambahkan', 
            'data' => $product
        ], 201);
    }

    // UPDATE: Edit produk
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'string',
            'color' => 'nullable|string|max:50', // <--- TAMBAHKAN VALIDASI COLOR DI SINI
            'price' => 'numeric',
            'personality_type' => 'nullable|in:prestige,peaceful_calm,rebel_brave,sweet_shy',
            'top_note' => 'nullable|string|max:255',
            'middle_note' => 'nullable|string|max:255',
            'base_note' => 'nullable|string|max:255',
            'image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_4' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'image_produk_belanja' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:70048',
            'bottle_size' => 'integer',
            'perfume_type' => 'string|max:255',
            'gender' => 'in:unisex,male,female',
            'quantity' => 'integer',
            'stock_status' => 'in:tersedia,minim,habis',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image_1', 'image_2', 'image_3', 'image_4', 'image_produk_belanja']);

        $imageFields = ['image_1', 'image_2', 'image_3', 'image_4', 'image_produk_belanja'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                // Hapus gambar lama
                if ($product->$field) {
                    Storage::disk('public')->delete($product->$field);
                }
                // Simpan gambar baru
                $data[$field] = $request->file($field)->store('products', 'public');
            }
        }

        $product->update($data);

        return response()->json([
            'success' => true, 
            'message' => 'Produk berhasil diupdate', 
            'data' => $product
        ], 200);
    }

    // DELETE: Hapus produk
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
        }

        $imageFields = ['image_1', 'image_2', 'image_3', 'image_4', 'image_produk_belanja'];
        foreach ($imageFields as $field) {
            if ($product->$field) {
                Storage::disk('public')->delete($product->$field);
            }
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Produk berhasil dihapus'], 200);
    }
}