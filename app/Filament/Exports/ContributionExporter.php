<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Exporter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\ExportColumn;
use App\Models\Contribution;

class ContributionExporter extends Exporter
{
    protected static ?string $model = Contribution::class;


    public static function getQuery(Export $export): Builder
    {
        $filters = $export->getFilters();


        return Contribution::query()
            ->with('payer') // this ensures your accessor works properly
            ->when(isset($filters['status']), fn($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['month']), fn($query) => $query->whereMonth('payment_date', $filters['month']))
            ->when(isset($filters['year']), fn($query) => $query->whereYear('payment_date', $filters['year']));
    }


    public static function getColumns(): array
    {
        return [
            ExportColumn::make('consid')->label('Contribution ID'),
            ExportColumn::make('name')->label('Payer Name'),

            ExportColumn::make('deceasedID')->label('Deceased ID'),
            ExportColumn::make('amount'),
            ExportColumn::make('adjusted_amount')->label('Adjusted Amount'),
            ExportColumn::make('payment_date'),
            ExportColumn::make('status_text')->label('Status'),
            ExportColumn::make('remarks'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your contribution export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
