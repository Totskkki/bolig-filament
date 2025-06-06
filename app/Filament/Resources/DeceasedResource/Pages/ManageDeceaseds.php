<?php

namespace App\Filament\Resources\DeceasedResource\Pages;

use App\Filament\Resources\DeceasedResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use App\Models\Contribution;
use App\Models\Member;
use App\Jobs\GenerateContributionsForDeceased;
use Filament\Notifications\Notification;

class ManageDeceaseds extends ManageRecords
{
    protected static string $resource = DeceasedResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()

                ->after(function ($record) {
                      \Log::info('Triggered once for deceasedID: ' . $record->deceasedID);


                    $member = \App\Models\Member::find($record->memberID);
                    if ($member) {
                        $member->membership_status = 'deceased';
                        $member->save();
                    }



                    // \Illuminate\Support\Facades\Bus::chain([
                    //     new GenerateContributionsForDeceased($record->deceasedID),
                    //     new \App\Jobs\UpdateAdjustedAmounts,
                    // ])->dispatch();

                        GenerateContributionsForDeceased::dispatch($record->deceasedID);


                    // Optional: notify the user
                    Notification::make()
                        ->title('Processing contributions...')
                        ->body('Contributions are being generated in the background.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
