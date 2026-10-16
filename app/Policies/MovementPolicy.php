<?php

namespace App\Policies;

use App\Models\Movement;
use App\Models\User;

class MovementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Movement $movement): bool
    {
        return true;
    }

    /** Clerks record check-outs and check-ins through the dedicated actions. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Correcting history is reserved for administrators. */
    public function update(User $user, Movement $movement): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Movement $movement): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
