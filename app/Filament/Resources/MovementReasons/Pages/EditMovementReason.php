<?php

namespace App\Filament\Resources\MovementReasons\Pages;

use App\Filament\Resources\MovementReasons\MovementReasonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMovementReason extends EditRecord
{
    protected static string $resource = MovementReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
