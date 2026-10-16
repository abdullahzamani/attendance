<?php

namespace Tests\Feature;

use App\Actions\CheckInStaff;
use App\Actions\CheckOutStaff;
use App\Exceptions\MovementException;
use App\Models\Movement;
use App\Models\MovementReason;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function checkOut(Staff $staff, array $overrides = []): Movement
    {
        $args = $overrides + [
            'reasonId' => MovementReason::factory()->create()->id,
            'outAt' => now(),
        ];

        return app(CheckOutStaff::class)->handle($staff, ...$args);
    }

    public function test_check_out_opens_a_movement(): void
    {
        $staff = Staff::factory()->create();
        $clerk = User::factory()->create();

        $movement = $this->checkOut($staff, ['note' => 'Site visit', 'by' => $clerk]);

        $this->assertTrue($movement->isOpen());
        $this->assertTrue($staff->isOut());
        $this->assertSame('Site visit', $movement->note);
        $this->assertTrue($movement->checkedOutBy->is($clerk));
    }

    public function test_staff_cannot_be_checked_out_twice(): void
    {
        $staff = Staff::factory()->create();
        $this->checkOut($staff);

        $this->expectException(MovementException::class);
        $this->checkOut($staff);
    }

    public function test_expected_return_must_be_after_check_out(): void
    {
        $this->expectException(MovementException::class);

        $this->checkOut(Staff::factory()->create(), ['expectedBackAt' => now()->subHour()]);
    }

    public function test_check_in_closes_the_movement(): void
    {
        $staff = Staff::factory()->create();
        $movement = $this->checkOut($staff, ['outAt' => now()->subHour()]);

        app(CheckInStaff::class)->handle($movement, now());

        $this->assertFalse($movement->fresh()->isOpen());
        $this->assertFalse($staff->isOut());
    }

    public function test_a_returned_movement_cannot_be_checked_in_again(): void
    {
        $movement = Movement::factory()->create();

        $this->expectException(MovementException::class);
        app(CheckInStaff::class)->handle($movement, now());
    }

    public function test_check_in_time_cannot_be_before_check_out_time(): void
    {
        $movement = Movement::factory()->open()->create(['out_at' => now()]);

        $this->expectException(MovementException::class);
        app(CheckInStaff::class)->handle($movement, now()->subMinute());
    }

    public function test_overdue_is_only_flagged_for_open_movements_past_expected_return(): void
    {
        $this->assertTrue(Movement::factory()->open()->make(['expected_back_at' => now()->subMinute()])->isOverdue());
        $this->assertFalse(Movement::factory()->open()->make(['expected_back_at' => now()->addHour()])->isOverdue());
        $this->assertFalse(Movement::factory()->open()->make(['expected_back_at' => null])->isOverdue());
        $this->assertFalse(Movement::factory()->make(['expected_back_at' => now()->subMinute()])->isOverdue());
    }
}
