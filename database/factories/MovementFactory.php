<?php

namespace Database\Factories;

use App\Models\Movement;
use App\Models\MovementReason;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
{
    public function definition(): array
    {
        $outAt = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'staff_id' => Staff::factory(),
            'movement_reason_id' => MovementReason::factory(),
            'out_at' => $outAt,
            'in_at' => (clone $outAt)->modify('+'.fake()->numberBetween(30, 240).' minutes'),
        ];
    }

    public function open(): static
    {
        return $this->state(['in_at' => null]);
    }
}
