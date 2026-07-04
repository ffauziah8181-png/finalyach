<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingsGoalFactory extends Factory
{
    protected $model = \App\Models\SavingsGoal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama_target' => 'Dana Darurat',
            'nominal_target' => 10000000,
            'nominal_terkumpul' => 0,
            'status' => 'berjalan',
        ];
    }
}
