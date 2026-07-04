<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = \App\Models\Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'tipe' => 'pengeluaran',
            'jumlah' => fake()->numberBetween(10000, 500000),
            'catatan' => fake()->sentence(3),
            'tanggal' => now()->toDateString(),
        ];
    }
}
