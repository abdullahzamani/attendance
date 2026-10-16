<?php

namespace App\Actions;

use App\Exceptions\MovementException;
use App\Models\Movement;
use App\Models\User;
use Carbon\CarbonInterface;

class CheckInStaff
{
    public function handle(Movement $movement, CarbonInterface $inAt, ?User $by = null): Movement
    {
        if (! $movement->isOpen()) {
            throw new MovementException("{$movement->staff->name} has already checked in.");
        }

        if ($inAt->lessThan($movement->out_at)) {
            throw new MovementException('Check-in time cannot be before the check-out time.');
        }

        $movement->update([
            'in_at' => $inAt,
            'checked_in_by' => $by?->getKey(),
        ]);

        return $movement;
    }
}
