<?php

namespace App\Filament\Widgets;

use App\Models\Movement;
use App\Models\Staff;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $active = Staff::active()->count();
        $out = Movement::open()->whereRelation('staff', 'is_active', true)->count();
        $overdue = Movement::open()->where('expected_back_at', '<', now())->count();

        return [
            Stat::make('In office', $active - $out)
                ->description("of {$active} active staff")
                ->color('success'),
            Stat::make('Out now', $out)
                ->color('danger'),
            Stat::make('Overdue', $overdue)
                ->description('past their expected return time')
                ->color($overdue > 0 ? 'warning' : 'gray'),
            Stat::make('Check-outs today', Movement::whereDate('out_at', today())->count()),
        ];
    }
}
