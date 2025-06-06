<?php

namespace App\Jobs;

use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Enums\ContributionStatus;

class GenerateContributionsForDeceased implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deceasedID;

    public function __construct($deceasedID)
    {
        $this->deceasedID = $deceasedID;
    }

    // public function handle(): void
    // {
    //     $deceased = Deceased::find($this->deceasedID);
    //     if (!$deceased) {
    //         \Log::error('Deceased not found for ID: ' . $this->deceasedID);
    //         return;
    //     }

    //     $totalDeaths = Deceased::count();
    //     $baseAmount = 15;
    //     $adjustedAmount = $totalDeaths * $baseAmount;

    //     Member::whereDoesntHave('deceased')->chunk(100, function ($members) use ($deceased, $baseAmount, $adjustedAmount) {
    //         foreach ($members as $member) {
    //             Contribution::create([
    //                 'payer_memberID' => $member->memberID,
    //                 'deceasedID' => $deceased->deceasedID,
    //                 'amount' => $baseAmount,
    //                 'adjusted_amount' => $adjustedAmount,
    //                 'payment_date' => null,
    //                 'status' => 'pending',
    //                 'remarks' => 'Contribution for death',
    //             ]);

    //             $totalOwed = Contribution::where('payer_memberID', $member->memberID)
    //                 ->where('status', 'pending')
    //                 ->sum('amount');

    //             Contribution::where('payer_memberID', $member->memberID)
    //                 ->where('status', 'pending')
    //                 ->update(['adjusted_amount' => $totalOwed]);
    //         }
    //     });
    // }
    public function handle(): void
    {
        $deceased = Deceased::find($this->deceasedID);
        if (!$deceased) {
            \Log::error('Deceased not found for ID: ' . $this->deceasedID);
            return;
        }

        $baseAmount = 15;

        // Step 1: Create contributions for all non-deceased members
        $payerIDs = [];

        Member::whereDoesntHave('deceased')->chunk(100, function ($members) use ($deceased, $baseAmount, &$payerIDs) {
            foreach ($members as $member) {
                Contribution::create([
                    'payer_memberID' => $member->memberID,
                    'deceasedID' => $deceased->deceasedID,
                    'amount' => $baseAmount,
                    'adjusted_amount' => 0, // Temporarily 0
                    'payment_date' => null,
                    'status' => '0',
                    'remarks' => 'Contribution for death',
                ]);

                $payerIDs[] = $member->memberID;
            }
        });

        // Step 2: Update adjusted_amount for *all* members (not just current chunk)
        $allPayers = Contribution::where('status', '0')
            ->pluck('payer_memberID')
            ->unique();

        foreach ($allPayers as $payerID) {
            $totalOwed = Contribution::where('payer_memberID', $payerID)
               ->where('status', '0')
                ->sum('amount');

            Contribution::where('payer_memberID', $payerID)
                ->where('status', '0')
                ->update(['adjusted_amount' => $totalOwed]);
        }
    }
}
