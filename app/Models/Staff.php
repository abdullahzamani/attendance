<?php

namespace App\Models;

use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'ic_number', 'unit_id', 'position', 'grade', 'phone', 'is_active'])]
class Staff extends Model
{
    /** @use HasFactory<StaffFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return HasMany<Movement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    /**
     * The movement the staff member is currently out on, if any.
     *
     * @return HasOne<Movement, $this>
     */
    public function currentMovement(): HasOne
    {
        return $this->hasOne(Movement::class)->whereNull('in_at')->latestOfMany('out_at');
    }

    public function isOut(): bool
    {
        return $this->currentMovement()->exists();
    }

    /** @param Builder<Staff> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
