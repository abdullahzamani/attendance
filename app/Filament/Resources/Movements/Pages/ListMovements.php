<?php

namespace App\Filament\Resources\Movements\Pages;

use App\Filament\Actions\MovementActions;
use App\Filament\Resources\Movements\MovementResource;
use App\Models\Movement;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListMovements extends ListRecords
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn () => $this->exportCsv()),
            MovementActions::checkOut(),
        ];
    }

    /**
     * The old daily / monthly / yearly reports, as tabs over the same log.
     */
    public function getTabs(): array
    {
        return [
            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('out_at', today())),
            'month' => Tab::make('This month')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('out_at', [now()->startOfMonth(), now()->endOfMonth()])),
            'year' => Tab::make('This year')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('out_at', [now()->startOfYear(), now()->endOfYear()])),
            'all' => Tab::make('All'),
        ];
    }

    /**
     * Download whatever the table currently shows (tab, filters, search and sort applied).
     */
    protected function exportCsv(): StreamedResponse
    {
        $query = $this->getFilteredSortedTableQuery()->with(['staff.unit', 'reason', 'checkedOutBy', 'checkedInBy']);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel reads the file as UTF-8.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Staff', 'IC number', 'Unit', 'Reason', 'Note', 'Out', 'Expected back', 'Back', 'Minutes out', 'Recorded by', 'Checked in by']);

            $query->lazy()->each(function (Movement $m) use ($out) {
                fputcsv($out, array_map($this->csvSafe(...), [
                    $m->staff->name,
                    $m->staff->ic_number,
                    $m->staff->unit->code,
                    $m->reason->name,
                    $m->note,
                    $m->out_at->format('Y-m-d H:i'),
                    $m->expected_back_at?->format('Y-m-d H:i'),
                    $m->in_at?->format('Y-m-d H:i'),
                    $m->in_at ? (int) $m->out_at->diffInMinutes($m->in_at) : null,
                    $m->checkedOutBy?->name,
                    $m->checkedInBy?->name,
                ]));
            });

            fclose($out);
        }, 'movements-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Stop spreadsheet apps from running text such as "=HYPERLINK(...)" typed into a note as a formula.
     */
    protected function csvSafe(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
