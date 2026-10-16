<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends AdminOnlyPolicy
{
    /** Admins can't delete their own account and lock everyone out. */
    public function delete(User $user, Model $model): bool
    {
        return $user->isAdmin() && $user->isNot($model);
    }
}
