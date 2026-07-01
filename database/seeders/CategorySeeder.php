<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $pengeluaran = [
            ['nama' => 'Makanan', 'icon' => 'utensils', 'warna' => '#F97316'],
            ['nama' => 'Transport', 'icon' => 'car', 'warna' => '#3B82F6'],
            ['nama' => 'Belanja', 'icon' => 'shopping-bag', 'warna' => '#EC4899'],
            ['nama' => 'Tagihan', 'icon' => 'receipt', 'warna' => '#EF4444'],
            ['nama' => 'Hiburan', 'icon' => 'film', 'warna' => '#8B5CF6'],
            ['nama' => 'Kesehatan', 'icon' => 'heart-pulse', 'warna' => '#10B981'],
            ['nama' => 'Pendidikan', 'icon' => 'book', 'warna' => '#0EA5E9'],
            ['nama' => 'Lainnya', 'icon' => 'more-horizontal', 'warna' => '#6B7280'],
        ];

        $pemasukan = [
            ['nama' => 'Gaji Bulanan', 'icon' => 'wallet', 'warna' => '#22C55E'],
            ['nama' => 'Bonus', 'icon' => 'gift', 'warna' => '#22C55E'],
            ['nama' => 'Transfer Masuk', 'icon' => 'arrow-down-left', 'warna' => '#22C55E'],
            ['nama' => 'Lainnya', 'icon' => 'more-horizontal', 'warna' => '#22C55E'],
        ];

        foreach ($pengeluaran as $kat) {
            Category::create($kat + ['tipe' => 'pengeluaran', 'is_default' => true, 'user_id' => null]);
        }

        foreach ($pemasukan as $kat) {
            Category::create($kat + ['tipe' => 'pemasukan', 'is_default' => true, 'user_id' => null]);
        }
    }
}
