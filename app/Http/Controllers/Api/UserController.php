<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * READ: Mengambil data profil user yang sedang login.
     */
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data' => $request->user()
        ], 200);
    }

    /**
     * READ ALL: Mengambil semua user yang terdaftar (Untuk Admin)
     */
    public function getAllUsers()
    {
        // Mengambil semua user kecuali data yang sensitif (password, token, dll otomatis tersembunyi)
        $users = \App\Models\User::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Semua data user berhasil diambil.',
            'data' => $users
        ], 200);
    }

    /**
     * UPDATE: Memperbarui data profil user (termasuk nama & alamat lengkap).
     */
    // File: app/Http/Controllers/Api/UserController.php
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nama_lengkap' => ['nullable', 'string', 'max:255'],
            'alamat_lengkap' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'], // Tambahkan validasi phone
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'avatar_profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // Validasi gambar
        ]);

        // Handle File Upload
        if ($request->hasFile('avatar_profile')) {
            $path = $request->file('avatar_profile')->store('avatars', 'public');
            $validated['avatar_profile'] = $path;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ], 200);
    }

    /**
     * DELETE: Menghapus akun user yang sedang login secara permanen.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Hapus token auth aktif terlebih dahulu (Jika menggunakan Laravel Sanctum)
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Hapus user dari database
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun Anda telah berhasil dihapus secara permanen.'
        ], 200);
    }

    /**
     * Mengambil riwayat belanja user
     */
    public function shoppingHistory(Request $request)
    {
        $user = $request->user();

        // Pastikan namespace Model Order sesuai dengan lokasi file Anda
        $history = \App\Models\Order::with('product') // Eager loading untuk mengambil data produk
            ->where('user_id', $user->id)             // Memastikan hanya mengambil data milik user yang login
            ->orderBy('created_at', 'desc')           // Urutkan dari yang terbaru
            ->get();

        return response()->json($history);
    }
}
