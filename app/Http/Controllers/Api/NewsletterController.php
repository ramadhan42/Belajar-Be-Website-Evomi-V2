<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // 1. Validasi input email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar pada buletin kami.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first('email'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // 2. Simpan ke database
            $subscriber = Subscriber::create([
                'email' => $request->email,
            ]);

            // 3. Kembalikan response sukses
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mendaftar buletin! Terima kasih telah berlangganan.',
                'data' => $subscriber
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}