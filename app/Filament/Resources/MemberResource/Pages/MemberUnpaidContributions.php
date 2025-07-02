<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Models\Contribution;
use App\Models\Member;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class MemberUnpaidContributions extends Page
{
    public Member $record;

    public ?int $selectedContribution = null;

    protected static string $resource = \App\Filament\Resources\MemberResource::class;

    protected static string $view = 'filament.resources.member-resource.pages.member-unpaid-contributions';

    public function mount(Member $record): void
    {
        $this->record = $record->load('unpaidContributions.deceased');
    }

    public function pay(int $consid): void
    {
        $contribution = Contribution::findOrFail($consid);

        // Step 1: Generate batch manually
        $paymentBatch = uniqid('batch_');

        // Step 2: Save it
        $contribution->payment_batch = $paymentBatch;
        $contribution->status = 1;
        $contribution->payment_date = now();
        $contribution->save();

        // Step 3: Refresh to ensure it's updated
        $contribution->refresh();

        // Step 4: Get values
        $payer = $contribution->payer_memberID; // NOT ->payer?->memberID
        $batch = $contribution->payment_batch;

        // Step 5: Log
        logger()->info('Dispatching receiptReady', [
            'payer' => $payer,
            'batch' => $batch,
        ]);


        Notification::make()
            ->title('Payment Successful')
            ->body("Receipt for Contribution has been generated.")
            ->success()
            ->duration(3000) // 3 seconds
            ->send();

        // Step 6: Dispatch browser event
        $this->js(<<<JS
        window.dispatchEvent(new CustomEvent('receiptReady', {
            detail: {
                payer: {$payer},
                batch: "{$batch}"
            }
        }));
    JS);
    }
}
