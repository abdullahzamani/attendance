<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    /** Full access, including user accounts and master data. */
    case Admin = 'admin';

    /** Records check-outs/check-ins and views reports. */
    case Clerk = 'clerk';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Clerk => 'Clerk',
        };
    }
}
