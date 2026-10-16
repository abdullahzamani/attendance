<?php

namespace App\Filament\Actions;

use App\Actions\CheckInStaff;
use App\Actions\CheckOutStaff;
use App\Exceptions\MovementException;
use App\Models\Movement;
use App\Models\MovementReason;
use App\Models\Staff;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

/**
 * The two everyday actions of the app, shared by the dashboard, staff list and movement log.
 */
class MovementActions
{
    /**
     * Check a staff member out of the office.
     *
     * Without a record (page header action) the form asks which staff member is leaving.
     * On a Staff table row, the row's record is used.
     */
    public static function checkOut(string $name = 'checkOut'): Action
    {
        return Action::make($name)
            ->label('Check out')
            ->icon(Heroicon::OutlinedArrowRightStartOnRectangle)
            ->color('danger')
            ->modalHeading(fn (?Staff $record) => $record ? "Check out {$record->name}" : 'Check out staff')
            ->modalSubmitActionLabel('Check out')
            ->visible(fn (?Staff $record) => $record === null || ($record->is_active && ! $record->currentMovement))
            ->schema(fn (?Staff $record) => [
                Select::make('staff_id')
                    ->label('Staff')
                    ->options(fn () => Staff::active()
                        ->whereDoesntHave('movements', fn ($query) => $query->open())
                        ->with('unit')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Staff $staff) => [$staff->id => "{$staff->name} ({$staff->unit->code})"]))
                    ->searchable()
                    ->required()
                    ->hidden($record !== null),
                Select::make('movement_reason_id')
                    ->label('Reason')
                    ->options(fn () => MovementReason::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->required(),
                DateTimePicker::make('out_at')
                    ->label('Out at')
                    ->seconds(false)
                    ->default(now())
                    ->required(),
                DateTimePicker::make('expected_back_at')
                    ->label('Expected back')
                    ->seconds(false)
                    ->after('out_at'),
                Textarea::make('note')
                    ->rows(3)
                    ->maxLength(1000),
            ])
            ->action(function (array $data, ?Staff $record, Action $action, CheckOutStaff $checkOut) {
                $staff = $record ?? Staff::findOrFail($data['staff_id']);

                try {
                    $checkOut->handle(
                        staff: $staff,
                        reasonId: (int) $data['movement_reason_id'],
                        outAt: Carbon::parse($data['out_at']),
                        expectedBackAt: filled($data['expected_back_at'] ?? null) ? Carbon::parse($data['expected_back_at']) : null,
                        note: $data['note'] ?? null,
                        by: auth()->user(),
                    );
                } catch (MovementException $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();
                    $action->halt();
                }

                Notification::make()->success()->title("{$staff->name} checked out")->send();
            });
    }

    /**
     * Check a staff member back in.
     *
     * @param  Closure(mixed): ?Movement  $resolveMovement  Finds the open movement from the table row record.
     */
    public static function checkIn(Closure $resolveMovement, string $name = 'checkIn'): Action
    {
        return Action::make($name)
            ->label('Check in')
            ->icon(Heroicon::OutlinedArrowLeftEndOnRectangle)
            ->color('success')
            ->modalHeading(fn ($record) => 'Check in '.$resolveMovement($record)?->staff->name)
            ->modalSubmitActionLabel('Check in')
            ->visible(fn ($record) => $resolveMovement($record)?->isOpen() ?? false)
            ->schema([
                DateTimePicker::make('in_at')
                    ->label('Back at')
                    ->seconds(false)
                    ->default(now())
                    ->required(),
            ])
            ->action(function (array $data, $record, Action $action, CheckInStaff $checkIn) use ($resolveMovement) {
                $movement = $resolveMovement($record);

                try {
                    $checkIn->handle($movement, Carbon::parse($data['in_at']), auth()->user());
                } catch (MovementException $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();
                    $action->halt();
                }

                Notification::make()->success()->title("{$movement->staff->name} checked in")->send();
            });
    }
}
