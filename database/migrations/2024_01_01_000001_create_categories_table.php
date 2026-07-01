<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                 // Makanan, Transport, Belanja, Tagihan, Gaji, dst
            $table->enum('tipe', ['pengeluaran', 'pemasukan']);
            $table->string('icon')->nullable();      // nama icon utk mobile app
            $table->string('warna')->nullable();     // hex warna utk chart
            $table->boolean('is_default')->default(true); // kategori bawaan sistem
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // null = kategori global
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
