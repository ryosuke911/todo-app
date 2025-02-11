<?php

namespace Database\Factories;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    protected $model = Todo::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'deadline' => $this->faker->dateTimeBetween('now', '+1 year'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 