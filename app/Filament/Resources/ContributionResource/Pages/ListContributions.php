<?php

namespace App\Filament\Resources\ContributionResource\Pages;

use App\Filament\Resources\ContributionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContributions extends ListRecords
{
    protected static string $resource = ContributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // protected static function getTableRecordKeyName(): string
    // {
    //     return 'payer_memberID'; // use the grouped unique key
    // }
}
