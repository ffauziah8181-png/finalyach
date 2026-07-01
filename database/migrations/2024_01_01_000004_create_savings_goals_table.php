<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_target');
            $table->string('kategori_icon')->nullable();
            $table->string('foto_sampul')->nullable();
            $table->decimal('nominal_target', 15, 2);
            $table->decimal('nominal_terkumpul', 15, 2)->default(0);
            $table->date('tanggal_target')->nullable();
            $table->boolean('nabung_otomatis')->default(false);
            $table->decimal('nominal_otomatis', 15, 2)->nullable();
            $table->enum('frekuensi_otomatis', ['harian', 'mingguan', 'bulanan'])->nullable();
            $table->time('waktu_otomatis')->nullable();
            $table->enum('status', ['berjalan', 'tercapai'])->default('berjalan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
