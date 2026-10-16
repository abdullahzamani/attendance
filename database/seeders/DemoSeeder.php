<?php

namespace Database\Seeders;

use App\Models\Movement;
use App\Models\MovementReason;
use App\Models\Staff;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Fill the app with sample staff and movements for trying it out locally.
     */
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $reasons = MovementReason::pluck('id');

        foreach (Unit::all() as $unit) {
            Staff::factory()->count(6)->for($unit)->create()->each(function (Staff $staff) use ($reasons) {
                Movement::factory()->count(8)->for($staff)
                    ->state(fn () => ['movement_reason_id' => $reasons->random()])
                    ->create();
            });
        }

        // A few people currently out of the office.
        Staff::inRandomOrder()->take(5)->get()->each(function (Staff $staff) use ($reasons) {
            Movement::factory()->open()->for($staff)->create([
                'movement_reason_id' => $reasons->random(),
                'out_at' => now()->subMinutes(rand(10, 120)),
                'expected_back_at' => now()->addMinutes(rand(-30, 120)),
            ]);
        });
    }
}
