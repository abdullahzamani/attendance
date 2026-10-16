<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('staff_count')
                    ->label('Staff')
                    ->counts('staff')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Display order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                // Units that still have staff can't be removed (the foreign key restricts it).
                DeleteAction::make()
                    ->hidden(fn ($record) => $record->staff()->exists()),
            ]);
    }
}
