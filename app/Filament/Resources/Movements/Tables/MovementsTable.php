<?php

namespace App\Filament\Resources\Movements\Tables;

use App\Filament\Actions\MovementActions;
use App\Models\Movement;
use Carbon\CarbonInterface;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['staff.unit', 'reason']))
            ->columns([
                TextColumn::make('staff.name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('staff.unit.code')
                    ->label('Unit')
                    ->badge(),
                TextColumn::make('reason.name')
                    ->label('Reason')
                    ->description(fn (Movement $record) => $record->note)
                    ->wrap(),
                TextColumn::make('out_at')
                    ->label('Out')
                    ->dateTime('d M Y, g:i A')
                    ->sortable(),
                TextColumn::make('expected_back_at')
                    ->label('Expected back')
                    ->time('g:i A')
                    ->toggleable(),
                TextColumn::make('in_at')
                    ->label('Back')
                    ->dateTime('d M Y, g:i A')
                    ->placeholder('Still out')
                    ->sortable(),
                TextColumn::make('duration')
                    ->state(fn (Movement $record) => $record->out_at->diffForHumans($record->in_at ?? now(), [
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                        'parts' => 2,
                        'minimumUnit' => 'minute',
                    ]))
                    ->toggleable(),
                TextColumn::make('status')
                    ->state(fn (Movement $record) => match (true) {
                        $record->isOverdue() => 'Overdue',
                        $record->isOpen() => 'Out',
                        default => 'Returned',
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Overdue' => 'warning',
                        'Out' => 'danger',
                        default => 'success',
                    }),
                TextColumn::make('checkedOutBy.name')
                    ->label('Recorded by')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('out_at', 'desc')
            ->filters([
                TernaryFilter::make('in_at')
                    ->label('Status')
                    ->nullable()
                    ->placeholder('All')
                    ->trueLabel('Returned')
                    ->falseLabel('Still out'),
                SelectFilter::make('unit')
                    ->relationship('staff.unit', 'code')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('movement_reason_id')
                    ->label('Reason')
                    ->relationship('reason', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name')
                    ->searchable(),
                Filter::make('out_between')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('out_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('out_at', '<=', $date)))
                    ->indicateUsing(fn (array $data) => array_filter([
                        ($data['from'] ?? null) ? 'From '.$data['from'] : null,
                        ($data['until'] ?? null) ? 'Until '.$data['until'] : null,
                    ])),
            ])
            ->recordActions([
                MovementActions::checkIn(fn (Movement $record) => $record),
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
