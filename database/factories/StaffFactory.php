<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'ic_number' => fake()->unique()->numerify('############'),
            'unit_id' => Unit::factory(),
            'position' => fake()->jobTitle(),
            'grade' => fake()->randomElement(['J54', 'J52', 'J48', 'J41', 'JA38', 'JA36', 'JA29', 'N17']),
            'phone' => fake()->numerify('01#-#######'),
        ];
    }
}
