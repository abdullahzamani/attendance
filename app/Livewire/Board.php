<?php

namespace App\Livewire;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Wall display: every unit's staff with their in/out status, refreshed by polling.
 *
 * Replaces the five near-identical adminPaparan*.php pages. Show one unit with ?unit=UGT.
 */
#[Layout('layouts.board')]
#[Title('Display board')]
class Board extends Component
{
    #[Url]
    public ?string $unit = null;

    /** @return Collection<int, Unit> */
    public function units(): Collection
    {
        return Unit::query()
            ->when($this->unit, fn ($query) => $query->where('code', $this->unit))
            ->with(['staff' => fn ($query) => $query->active()->orderBy('name')->with('currentMovement.reason')])
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        return view('livewire.board', ['units' => $this->units()]);
    }
}
