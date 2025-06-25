<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


class Contribution extends Model
{
    protected $table = 'contributions';
    protected $appends = ['name', 'total_unpaid_amount', 'latest_unpaid_status'];


    protected $primaryKey = 'consid';
    protected $fillable = [

        'payer_memberID',
        'deceased_id',
        'amount',
        'adjusted_amount',
        'status',
        'payment_date',
        'month',
        'year',
        'remarks',
    ];

    public function payer()
    {
        return $this->belongsTo(Member::class, 'payer_memberID', 'memberID');
    }


    public function deceased()
    {
        return $this->belongsTo(Deceased::class, 'deceased_id', 'deceasedID');
    }

    public function getNameAttribute()
    {
        $name = optional($this->payer?->name);

        return $name ? "{$name->last_name}, {$name->first_name} {$name->middle_name}" : null;
    }


    public function latestContribution()
    {
        return $this->hasOne(Contribution::class, 'payer_memberID')->latest('payment_date');
    }



    public function getGroupedDeceasedNamesAttribute()
    {
        $ids = explode(',', $this->deceased_ids ?? '');
        return Deceased::whereIn('deceasedID', $ids)
            ->with('member.name')
            ->get()
            ->map(fn($d) => optional($d->member->name)?->full_name)
            ->filter()
            ->unique()
            ->implode('<br>');
    }

    public function scopeGroupedContributions($query)
    {
        return $query->selectRaw('payer_memberID, MAX(payment_date) as last_payment_date')
            ->selectRaw('SUM(CASE WHEN status != 1 THEN amount ELSE 0 END) as unpaid_amount')
            ->selectRaw('GROUP_CONCAT(DISTINCT deceased_id) as deceased_ids')
            ->with(['payer.name', 'deceased.member.name'])
            ->groupBy('payer_memberID')
            ->havingRaw('unpaid_amount > 0');
    }


    public function unpaidContributions()
    {
        return $this->hasMany(Contribution::class, 'payer_memberID')->where('status', '!=', 1);
    }




    public function hasPaidContributions()
    {
        return $this->contributions()->where('status', 1)->exists();
    }

    public function getTotalUnpaidAmountAttribute()
    {
        return $this->attributes['total_unpaid_amount'] ?? 0;
    }
    public function getLatestUnpaidStatusAttribute()
    {
        return $this->attributes['latest_unpaid_status'] ?? null;
    }


    public function getLastPaymentDateAttribute()
    {
        return $this->contributions()
            ->latest('payment_date')
            ->value('payment_date');
    }
    // public function scopeFilteredGrouped($query, $filters = [])
    // {
    //     $year = $filters['year'] ?? null;
    //     $month = $filters['month'] ?? null;
    //     $status = $filters['status'] ?? null;

    //     $query->selectRaw('MAX(consid) as consid')
    //         ->selectRaw('payer_memberID, MAX(payment_date) as payment_date')
    //         ->selectRaw('SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid_amount')
    //         ->selectRaw('MAX(status) as latest_unpaid_status')
    //         ->selectRaw('GROUP_CONCAT(DISTINCT deceased_id) as deceased_ids')
    //         ->selectRaw("GROUP_CONCAT(DISTINCT remarks SEPARATOR '; ') as remarks")
    //         ->with(['payer.name', 'deceased.member.name'])
    //         ->groupBy('payer_memberID');

    //     // Apply status filter here based on latest_unpaid_status
    //     if (!is_null($status) && $status !== '') {
    //         $query->having('latest_unpaid_status', '=', $status);
    //     }

    //     if (!empty($month)) {
    //         $query->whereMonth('payment_date', $month);
    //     }

    //     if (!empty($year)) {
    //         $query->whereYear('payment_date', $year);
    //     }

    //     return $query;
    // }
    public function scopeFilteredGrouped($query, $filters = [])
    {
        $year = $filters['year']['value'] ?? $filters['year'] ?? null;
        $month = $filters['month']['value'] ?? $filters['month'] ?? null;
        $status = $filters['status']['value'] ?? $filters['status'] ?? null;

        // Use integer comparison — your DB columns are integers
        if (!empty($month)) {
            $query->where('month', (int) $month);
        }

        if (!empty($year)) {
            $query->where('year', (int) $year);
        }

        $query->selectRaw('MAX(consid) as consid')
            ->selectRaw('payer_memberID')
            ->selectRaw('MAX(payment_date) as payment_date')
            ->selectRaw('SUM(CASE WHEN status = 0 THEN amount ELSE 0 END) as total_unpaid_amount')
            ->selectRaw('SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as unpaid_count')
            ->selectRaw('MAX(status) as latest_unpaid_status')
            ->selectRaw('GROUP_CONCAT(DISTINCT deceased_id) as deceased_ids')
            ->selectRaw("GROUP_CONCAT(DISTINCT remarks SEPARATOR '; ') as remarks")
            ->with(['payer.name'])
            ->groupBy('payer_memberID');

        // Only apply HAVING if filter status is set
        if (!is_null($status) && $status !== '') {
            if ((int)$status === 0) {
                $query->having('unpaid_count', '>', 0);
            } else {
                $query->having('unpaid_count', '=', 0);
            }
        }

        return $query;
    }
}
