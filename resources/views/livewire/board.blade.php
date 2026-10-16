<div wire:poll.15s>
    <header>
        <h1>{{ config('app.name') }}</h1>
        <div class="clock">{{ now()->format('l, j F Y · g:i A') }}</div>
    </header>

    <main>
        @forelse ($units as $unit)
            @php($out = $unit->staff->filter->currentMovement)
            <section wire:key="unit-{{ $unit->id }}">
                <h2>{{ $unit->code }}</h2>
                <p class="summary">
                    {{ $unit->name !== $unit->code ? $unit->name.' · ' : '' }}{{ $unit->staff->count() - $out->count() }} in, {{ $out->count() }} out
                </p>
                <ul>
                    @forelse ($unit->staff as $staff)
                        @php($movement = $staff->currentMovement)
                        <li class="{{ $movement ? ($movement->isOverdue() ? 'out late' : 'out') : 'in' }}">
                            <span class="dot" aria-hidden="true"></span>
                            <div>
                                <div>{{ $staff->name }}</div>
                                @if ($movement)
                                    <div class="detail">{{ $movement->reason->name }}{{ $movement->note ? ' — '.$movement->note : '' }}</div>
                                @endif
                            </div>
                            <div class="time">
                                @if ($movement)
                                    Out {{ $movement->out_at->format('g:i A') }}
                                    @if ($movement->expected_back_at)
                                        <br>Back {{ $movement->expected_back_at->format('g:i A') }}
                                    @endif
                                @else
                                    In office
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="empty">No active staff.</li>
                    @endforelse
                </ul>
            </section>
        @empty
            <p class="empty">No units to show.</p>
        @endforelse
    </main>
</div>
