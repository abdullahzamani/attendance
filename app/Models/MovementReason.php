<?php

namespace App\Models;

use Database\Factories\MovementReasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'is_active'])]
class MovementReason extends Model
{
    /** @use HasFactory<MovementReasonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Movement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
