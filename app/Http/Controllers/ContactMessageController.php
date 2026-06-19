<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    // Fungsi GET untuk menampilkan semua pesan
    public function index()
    {
        try {
            // Mengambil semua data pesan, diurutkan dari yang paling baru
            $messages = ContactMessage::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil semua data pesan.',
                'data'    => $messages
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Fungsi POST untuk menyimpan pesan baru (yang sudah Anda buat)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $contact = ContactMessage::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim! Tim kami akan segera menghubungi Anda.',
                'data'    => $contact
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.'
            ], 500);
        }
    }
}