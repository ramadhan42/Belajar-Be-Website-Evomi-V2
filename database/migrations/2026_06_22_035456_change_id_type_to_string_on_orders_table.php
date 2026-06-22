<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Di PostgreSQL, kita perlu menggunakan sintaks USING untuk mengkonversi integer ke varchar
        // sekaligus menghapus default auto-increment (sequence) pada kolom tersebut
        
        // 1. Hapus nilai default (auto-increment sequence)
        DB::statement('ALTER TABLE orders ALTER COLUMN id DROP DEFAULT');
        
        // 2. Ubah tipe data menjadi VARCHAR
        DB::statement('ALTER TABLE orders ALTER COLUMN id TYPE VARCHAR(255) USING id::VARCHAR');
    }

    public function down()
    {
        // Opsional: Cara mengembalikannya ke integer jika di-rollback
        // Catatan: Ini bisa gagal jika string mengandung huruf seperti "INV-000001"
        // DB::statement('ALTER TABLE orders ALTER COLUMN id TYPE BIGINT USING id::BIGINT');
    }
};