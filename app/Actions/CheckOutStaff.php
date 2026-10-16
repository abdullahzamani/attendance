<?php

namespace App\Actions;

use App\Exceptions\MovementException;
use App\Models\Movement;
use App\Models\Staff;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class CheckOutStaff
{
    public function handle(
        Staff $staff,
        int $reasonId,
        CarbonInterface $outAt,
        ?CarbonInterface $expectedBackAt = null,
        ?string $note = null,
        ?User $by = null,
    ): Movement {
        if ($expectedBackAt !== null && $expectedBackAt->lessThanOrEqualTo($outAt)) {
            throw new MovementException('Expected return time must be after the check-out time.');
        }

        return DB::transaction(function () use ($staff, $reasonId, $outAt, $expectedBackAt, $note, $by) {
            // Lock the staff row so two clerks can't check the same person out at once.
            Staff::whereKey($staff->getKey())->lockForUpdate()->first();

            if ($staff->movements()->open()->exists()) {
                throw new MovementException("{$staff->name} is already checked out.");
            }

            return $staff->movements()->create([
                'movement_reason_id' => $reasonId,
                'out_at' => $outAt,
                'expected_back_at' => $expectedBackAt,
                'note' => $note,
                'checked_out_by' => $by?->getKey(),
            ]);
        });
    }
}
