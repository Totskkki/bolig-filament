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
        $deceased = Deceased::find($this->deceasedID);
        if (!$deceased) {
            \Log::error('Deceased not found for ID: ' . $this->deceasedID);
            return;
        }

        $deceasedMember = Member::find($deceased->id);
        if (!$deceasedMember) {
            \Log::error('Member record not found for deceasedID: ' . $this->deceasedID);
            return;
        }

        // ✅ Get contribution amount from system settings
        $baseAmount = SystemSetting::where('key', 'mortuary_contribution')->value('value') ?? 15;

        // ✅ Insert contributions from all active members
        Member::where('membership_status', 0) // Only active members
            ->select('id')
            ->chunk(1000, function ($members) use ($deceased, $baseAmount) {
                $now = now();
                $month = $now->format('m');
                $year = $now->format('Y');
                $data = [];

                foreach ($members as $member) {
                    $data[] = [
                        'payer_memberID' => $member->id,
                        'deceasedID' => $deceased->id,
                        'amount' => $baseAmount,
                        'adjusted_amount' => 0,
                        'payment_date' => null,
                        'month' => $month,
                        'year' => $year,
                        'status' => 0, // unpaid
                        'remarks' => 'Contribution for death',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Contribution::insert($data); // ✅ bulk insert
            });
        \Log::info('Contribution records generated for deceasedID: ' . $this->deceasedID);
    }
}
