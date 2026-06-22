<?php

// Controller Library
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ContactMessageController;
use Illuminate\Support\Facades\Route;

// Public Routes

// Login & Register, AuthController
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Katalog Produk (Public)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/quiz/questions', [QuizController::class, 'getQuestions']);

// Route untuk pendaftaran buletin footer
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);

// Endpoint untuk mengambil semua pesan (GET)
Route::get('/contact', [ContactMessageController::class, 'index']);

// Endpoint untuk mengirim pesan baru (POST)
Route::post('/contact', [ContactMessageController::class, 'store']);

// Protected Routes (Butuh Login)
Route::middleware('auth:sanctum')->group(function () {

    // Logout, Authcontroller
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute Manajemen Profil User, UserController
    Route::get('/user/profile', [UserController::class, 'show']);     // Endpoint untuk Ambil Profil (Read)
    Route::put('/user/profile', [UserController::class, 'update']);   // Endpoint untuk Update Profil (Update)
    Route::delete('/user/profile', [UserController::class, 'destroy']); // Endpoint untuk Hapus Akun (Delete)
    // Route::get('/profile', [UserController::class, 'profile']);

    // Product Management (Idealnya ini diberi middleware khusus admin), ProductController
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']); // Menggunakan POST agar Form-Data file bisa terbaca
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Cart & Wishlist// Tambahkan atau pastikan ini ada di api.php di dalam middleware auth:sanctum
    Route::apiResource('carts', CartController::class)->only(['index', 'store', 'destroy', 'update']);
    Route::apiResource('wishlists', WishlistController::class)->only(['index', 'store', 'destroy']);

    // Quiz Actions, QuizController
    Route::post('/quiz/submit', [QuizController::class, 'submitQuiz']);
    Route::get('/quiz/history', [QuizController::class, 'history']);

    // Shopping Needs
    Route::get('/shopping-history', [UserController::class, 'shoppingHistory']);

    // Order Controller
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::patch('/orders/{id}/confirm', [OrderController::class, 'confirmReceipt']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

    // Tambahkan rute ini untuk mengubah status via Postman (Simulasi Admin)
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

});