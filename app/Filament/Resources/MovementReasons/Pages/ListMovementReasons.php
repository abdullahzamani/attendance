<?php

namespace App\Filament\Resources\MovementReasons\Pages;

use App\Filament\Resources\MovementReasons\MovementReasonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMovementReasons extends ListRecords
{
    protected static string $resource = MovementReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
