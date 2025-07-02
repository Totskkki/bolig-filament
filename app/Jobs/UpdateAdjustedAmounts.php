<?php

namespace App\Jobs;

use App\Enums\ContributionStatus;
use App\Models\Contribution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class UpdateAdjustedAmounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Chunk by payer_memberID to limit memory usage
        Contribution::where('status', '0')
            ->select('payer_memberID')
            ->distinct()
            ->chunk(100, function ($payerChunks) {
                foreach ($payerChunks as $payer) {
                    $payerID = $payer->payer_memberID;

                    $total = Contribution::where('payer_memberID', $payerID)
                        ->where('status', '0')
                        ->sum('amount');

                    Contribution::where('payer_memberID', $payerID)
                        ->where('status', '0')
                        ->update(['adjusted_amount' => $total]);
                }
            });
    }
}