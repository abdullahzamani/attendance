<?php

namespace App\Filament\Resources\MovementReasons;

use App\Filament\Resources\MovementReasons\Pages\CreateMovementReason;
use App\Filament\Resources\MovementReasons\Pages\EditMovementReason;
use App\Filament\Resources\MovementReasons\Pages\ListMovementReasons;
use App\Filament\Resources\MovementReasons\Schemas\MovementReasonForm;
use App\Filament\Resources\MovementReasons\Tables\MovementReasonsTable;
use App\Models\MovementReason;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MovementReasonResource extends Resource
{
    protected static ?string $model = MovementReason::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MovementReasonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovementReasonsTable::configure($table);
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
            'index' => ListMovementReasons::route('/'),
            'create' => CreateMovementReason::route('/create'),
            'edit' => EditMovementReason::route('/{record}/edit'),
        ];
    }
}
