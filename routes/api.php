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
use App\Http\Controllers\Api\OrderTrackingController;
use Illuminate\Support\Facades\Route;

// Public Routes

// Login & Register, AuthController
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// RUTE BARU: Endpoint khusus untuk menerima sinyal tutup browser dari Next.js Beacon
Route::post('/logout-beacon', [AuthController::class, 'logoutBeacon']);

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


Route::prefix('trackings')->group(function () {
    Route::get('/', [OrderTrackingController::class, 'index']); // Mendapatkan semua data
    Route::post('/', [OrderTrackingController::class, 'store']); // Membuat data baru
    Route::get('/{order_id}', [OrderTrackingController::class, 'show']); // Detail data spesifik
    Route::put('/{order_id}', [OrderTrackingController::class, 'update']); // Memperbarui data (Update)
    Route::delete('/{order_id}', [OrderTrackingController::class, 'destroy']); // Menghapus data
});

// ==========================================
// ADMIN DASHBOARD ROUTES
// ==========================================
Route::prefix('admin')->group(function () {
    Route::get('/orders', [OrderController::class, 'getAllOrders']);
    Route::get('/carts', [CartController::class, 'getAllCarts']);
    Route::get('/wishlists', [WishlistController::class, 'getAllWishlists']);

    // RUTE BARU:
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/revenue', [OrderController::class, 'getTotalRevenue']);

    Route::delete('/users/{id}', [UserController::class, 'destroyByAdmin']);

    // Endpoint untuk melihat semua email subscriber
    Route::get('/subscribers', [NewsletterController::class, 'index']);
});

// Protected Routes (Butuh Login)
Route::middleware('auth:sanctum')->group(function () {

    // Logout, Authcontroller
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute Manajemen Profil User, UserController
    Route::get('/user/profile', [UserController::class, 'show']);       // Endpoint untuk Ambil Profil (Read)
    Route::post('/user/profile', [UserController::class, 'update']);    // Endpoint untuk Update Profil (Update)
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
    // MASUKKAN KE SINI (Di dalam blok auth:sanctum)
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::patch('/orders/{id}/confirm', [OrderController::class, 'confirmReceipt']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // Tambahkan rute ini untuk mengubah status via Postman (Simulasi Admin)
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

});

