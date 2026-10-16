<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('ic_number')
                    ->label('IC number')
                    ->required()
                    ->regex('/^\d{12}$/')
                    ->validationMessages(['regex' => 'The IC number must be 12 digits without dashes.'])
                    ->unique(ignoreRecord: true)
                    ->placeholder('751208085857'),
                Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'name', fn ($query) => $query->orderBy('sort_order'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} — {$record->name}")
                    ->searchable(['code', 'name'])
                    ->preload()
                    ->required(),
                TextInput::make('position')
                    ->maxLength(255),
                TextInput::make('grade')
                    ->maxLength(10)
                    ->placeholder('J41'),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                Toggle::make('is_active')
                    ->label('Active')
                    ->helperText('Inactive staff are hidden from check-out and the display board.')
                    ->default(true),
            ]);
    }
}
