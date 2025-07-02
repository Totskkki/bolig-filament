<?php

namespace App\Jobs;

use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\Member;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class GenerateContributionsForDeceased implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deceasedID;

    public function __construct($deceasedID)
    {
        $this->deceasedID = $deceasedID;
    }

    public function handle(): void
    {
        // Load member and name via nested eager load
        $deceased = Deceased::with('member.name')->find($this->deceasedID);

        if (
            !$deceased ||
            !$deceased->member ||
            !$deceased->member->name
        ) {
            Log::error('Missing related name for deceased ID: ' . $this->deceasedID);
            return;
        }


        $deceasedName = optional($deceased->member->name)?->full_name ?? 'Unknown Deceased';



        Log::info('Deceased name:', [
            'deceasedID' => $this->deceasedID,
            'name' => $deceased?->member?->name
        ]);


        $baseAmount = SystemSetting::where('key', 'mortuary_contribution')->value('value') ?? 15;

        $now = now();
        //   $month = $now->format('m');
        $month = (int) $now->format('m');
        $year = $now->format('Y');

        Member::where('membership_status', 0)
            ->select('memberID')
            ->chunk(1000, function ($members) use ($deceased, $baseAmount, $deceasedName, $month, $year, $now) {
                $data = [];

                foreach ($members as $member) {
                    $data[] = [
                        'payer_memberID' => $member->memberID,
                        'deceased_id' => $deceased->deceasedID,
                        'amount' => $baseAmount,
                        'adjusted_amount' => 0,
                        'payment_date' => null,
                        'month' => $month,
                        'year' => $year,
                        'status' => 0,
                        'remarks' => 'Contribution for death of ' . $deceasedName,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Contribution::insert($data);
            });

        Log::info('Contribution records generated for deceasedID: ' . $this->deceasedID);
    }
}
