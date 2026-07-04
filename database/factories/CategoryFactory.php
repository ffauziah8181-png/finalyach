<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = \App\Models\Category::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement(['Makanan', 'Transport', 'Belanja', 'Tagihan']),
            'tipe' => 'pengeluaran',
            'icon' => 'tag',
            'warna' => '#3B82F6',
            'is_default' => true,
            'user_id' => null,
        ];
    }
}
