<?php

namespace App\Filament\Resources\Movements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

/**
 * Used only for administrators correcting a recorded movement.
 */
class MovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name')
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                Select::make('movement_reason_id')
                    ->label('Reason')
                    ->relationship('reason', 'name')
                    ->required(),
                DateTimePicker::make('out_at')
                    ->label('Out at')
                    ->seconds(false)
                    ->required(),
                DateTimePicker::make('expected_back_at')
                    ->label('Expected back')
                    ->seconds(false)
                    ->after('out_at'),
                DateTimePicker::make('in_at')
                    ->label('Back at')
                    ->seconds(false)
                    ->afterOrEqual('out_at')
                    ->helperText('Leave empty while the staff member is still out.'),
                Textarea::make('note')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }
}
