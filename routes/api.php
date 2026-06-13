<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/quiz/questions', [QuizController::class, 'getQuestions']); // Ambil soal & opsi kuis

// Protected Routes (Butuh Login)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    
    // Cart & Wishlist
    Route::apiResource('carts', CartController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('wishlists', WishlistController::class)->only(['index', 'store', 'destroy']);
    
    // Quiz Actions
    Route::post('/quiz/submit', [QuizController::class, 'submitQuiz']);
    Route::get('/quiz/history', [QuizController::class, 'history']); // Lihat histori kuis user
    
    // Shopping Needs / Histori Belanja
    Route::get('/shopping-history', [UserController::class, 'shoppingHistory']);
});