<?php
namespace App\Jobs;

use App\Models\Contribution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateAdjustedAmounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $payerIDs = Contribution::where('status', 0)->pluck('payer_memberID')->unique();

        foreach ($payerIDs as $payerID) {
            $total = Contribution::where('payer_memberID', $payerID)
                ->where('status', 0)
                ->sum('amount');

            Contribution::where('payer_memberID', $payerID)
                ->where('status', 0)
                ->update(['adjusted_amount' => $total]);
        }
    }
}
