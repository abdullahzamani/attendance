<?php

namespace App\Filament\Resources\Movements;

use App\Filament\Resources\Movements\Pages\EditMovement;
use App\Filament\Resources\Movements\Pages\ListMovements;
use App\Filament\Resources\Movements\Schemas\MovementForm;
use App\Filament\Resources\Movements\Tables\MovementsTable;
use App\Models\Movement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Movement log';

    public static function form(Schema $schema): Schema
    {
        return MovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovements::route('/'),
            'edit' => EditMovement::route('/{record}/edit'),
        ];
    }
}
