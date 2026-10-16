<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\MovementActions;
use App\Models\Movement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CurrentlyOut extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Currently out of office')
            ->query(Movement::query()->open()->with(['staff.unit', 'reason']))
            ->columns([
                TextColumn::make('staff.name')
                    ->label('Staff')
                    ->searchable(),
                TextColumn::make('staff.unit.code')
                    ->label('Unit')
                    ->badge(),
                TextColumn::make('reason.name')
                    ->label('Reason')
                    ->description(fn (Movement $record) => $record->note),
                TextColumn::make('out_at')
                    ->label('Out since')
                    ->since()
                    ->dateTimeTooltip('d M Y, g:i A')
                    ->sortable(),
                TextColumn::make('expected_back_at')
                    ->label('Expected back')
                    ->time('g:i A')
                    ->placeholder('—')
                    ->color(fn (Movement $record) => $record->isOverdue() ? 'warning' : null)
                    ->sortable(),
            ])
            ->defaultSort('out_at')
            ->emptyStateHeading('Everyone is in the office')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->headerActions([
                MovementActions::checkOut(),
            ])
            ->recordActions([
                MovementActions::checkIn(fn (Movement $record) => $record),
            ]);
    }
}
