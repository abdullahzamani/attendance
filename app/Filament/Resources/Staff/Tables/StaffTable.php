<?php

namespace App\Filament\Resources\Staff\Tables;

use App\Filament\Actions\MovementActions;
use App\Models\Staff;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['unit', 'currentMovement.reason']))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Staff $record) => $record->position),
                TextColumn::make('ic_number')
                    ->label('IC number')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('unit.code')
                    ->label('Unit')
                    ->badge()
                    ->sortable(),
                TextColumn::make('grade')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->state(fn (Staff $record) => $record->currentMovement ? 'Out — '.$record->currentMovement->reason->name : 'In office')
                    ->badge()
                    ->color(fn (Staff $record) => $record->currentMovement ? 'danger' : 'success'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('unit')
                    ->relationship('unit', 'code')
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('out')
                    ->label('Whereabouts')
                    ->placeholder('Everyone')
                    ->trueLabel('Out of office')
                    ->falseLabel('In office')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('movements', fn ($q) => $q->open()),
                        false: fn (Builder $query) => $query->whereDoesntHave('movements', fn ($q) => $q->open()),
                    ),
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])
            ->recordActions([
                MovementActions::checkOut(),
                MovementActions::checkIn(fn (Staff $record) => $record->currentMovement),
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
