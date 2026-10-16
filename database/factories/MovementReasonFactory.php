<?php

namespace Database\Factories;

use App\Models\MovementReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovementReason>
 */
class MovementReasonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
