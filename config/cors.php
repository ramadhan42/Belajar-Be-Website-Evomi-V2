<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Izinkan origin dari Next.js Anda. Jika pakai '*', pastikan supports_credentials bernilai false
    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000', '*'],

    'allowed_origins_patterns' => [],

    // INI YANG PALING PENTING: Harus mengizinkan semua header atau setidaknya 'Authorization'
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // Set ke false jika menggunakan Bearer Token biasa, bukan Cookie
];
