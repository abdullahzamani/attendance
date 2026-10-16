<?php

namespace App\Models;

use Database\Factories\MovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One trip out of the office: created on check-out, closed by setting in_at on check-in.
 */
#[Fillable([
    'staff_id', 'movement_reason_id', 'note', 'out_at', 'expected_back_at',
    'in_at', 'checked_out_by', 'checked_in_by',
])]
class Movement extends Model
{
    /** @use HasFactory<MovementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'out_at' => 'datetime',
            'expected_back_at' => 'datetime',
            'in_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Staff, $this> */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /** @return BelongsTo<MovementReason, $this> */
    public function reason(): BelongsTo
    {
        return $this->belongsTo(MovementReason::class, 'movement_reason_id');
    }

    /** @return BelongsTo<User, $this> */
    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /** @return BelongsTo<User, $this> */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function isOpen(): bool
    {
        return $this->in_at === null;
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->expected_back_at?->isPast();
    }

    /** @param Builder<Movement> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('in_at');
    }
}
