<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('dark_mode')->default(false);
            $table->string('bahasa', 5)->default('id');
            $table->boolean('notif_transaksi_masuk')->default(true);
            $table->boolean('notif_transaksi_keluar')->default(true);
            $table->boolean('notif_promo_info')->default(false);
            $table->boolean('login_biometrik')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
