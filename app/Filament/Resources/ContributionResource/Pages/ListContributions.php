<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Filament\Exports\ContributionExporter;
use App\Filament\Resources\ContributionResource;
use App\Models\Member;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\ExportAction;

class ListContributions extends ListRecords
{
    protected static string $resource = ContributionResource::class;
    protected static string $model = Member::class;

    protected function getHeaderActions(): array
    {

        return [
            Actions\CreateAction::make(),
            ExportAction::make()
                ->exporter(ContributionExporter::class)
        ];
        // return [
        //     // Actions\CreateAction::make(),
        //     // Action::make('summary')
        //     //     ->label(fn () => 'Paid: ' . \App\Models\Contribution::where('status', 1)->count()
        //     //         . ' | Unpaid: ' . \App\Models\Contribution::where('status', 0)->count())
        //     //     ->disabled()
        //     //     ->color('gray')
        //     //     ->extraAttributes(['style' => 'cursor: default']),

        //     // ExportAction::make('export')
        //     //     ->label('Export Records')
        //     //     ->color('primary'),
        // ];
    }

//        protected function getHeaderActions(): array
// {
//     return [
//         Actions\CreateAction::make(),

//         Action::make('summary')
//             ->label(fn () => 'Paid: ' . \App\Models\Contribution::where('status', 1)->count()
//                 . ' | Unpaid: ' . \App\Models\Contribution::where('status', 0)->count())
//             ->disabled()
//             ->color('gray')
//             ->extraAttributes(['style' => 'cursor: default']),

//         ExportAction::make('export')
//             ->label('Export Records')
//             ->exporter(ContributionExporter::class)
//             ->color('primary'),
//     ];
// }
    // protected static function getTableRecordKeyName(): string
    // {
    //     return 'payer_memberID'; // use the grouped unique key
    // }
}
