<?php

namespace App\Models;

use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\Users\Address;
use App\Models\Users\Name;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;


class Member extends Model
{


    protected $fillable = [

        'names_id',
        'address_id',
        'membership_date',
        'membership_status',
        'phone',
        'image_photo',
    ];


    public function name()
    {
        return $this->belongsTo(Name::class,'id' );
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'id');
    }
    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'id');
    }

    public function isDeceased()
    {
        return $this->deceased()->exists();
    }

    public function deceased()
    {
        return $this->hasMany(Deceased::class, 'id');
    }


    public function latestContribution()
{
    return $this->hasOne(Contribution::class, 'id')->latest('payment_date');
}



    public function getGroupedDeceasedNamesAttribute()
    {
        return $this->unpaidContributions()
            ->whereHas('deceased', fn($q) => $q->whereNotNull('id'))
            ->with('deceased.member.name')
            ->get()
            ->map(fn($c) => optional($c->deceased->member->name)?->full_name)
            ->filter()
            ->implode('<br>');
    }


    public function unpaidContributions()
    {
        return $this->hasMany(Contribution::class,'payer_memberID', 'id')->where('status', '!=', 1);
    }

    public function getTotalUnpaidAmountAttribute()
    {
        return $this->unpaidContributions()->sum('amount');
    }

    public function getLatestUnpaidStatusAttribute()
    {
        return $this->unpaidContributions->first()?->status;
    }
    public function hasPaidContributions()
    {
        return $this->contributions()->where('status', 1)->exists();
    }



    public function getLastPaymentDateAttribute()
{
    return $this->contributions()
        ->latest('payment_date')
        ->value('payment_date');
}






    protected static function booted()
    {
        // static::saved(function () {
        //     Cache::forget('member_status_counts');
        //     self::refreshStatusCountsCache();
        // });

        // static::deleted(function () {
        //     Cache::forget('member_status_counts');
        //     self::refreshStatusCountsCache();
        // });
        static::deleting(function ($member) {

            $member->name?->delete();
            $member->address?->delete();
        });
    }

    // public function getGroupedDeceasedNamesAttribute()
    // {
    //     return $this->unpaidContributions()
    //         ->whereHas('deceased', fn($q) => $q->whereNotNull('deceasedID'))
    //         ->with('deceased.member.name')
    //         ->get()
    //         ->map(fn($c) => optional($c->deceased->member->name)?->full_name)
    //         ->filter()
    //         ->implode(', ');
    // }
    // ✅ Cache refresh method
    // public static function refreshStatusCountsCache()
    // {
    //     Cache::remember('member_status_counts', now()->addMinutes(10), function () {
    //         return self::selectRaw('membership_status, COUNT(*) as total')
    //             ->groupBy('membership_status')
    //             ->pluck('total', 'membership_status');
    //     });
    // }

    // protected static function booted(): void
    // {
    //     static::deleting(function ($member) {
    //         $user = $member->name;

    //         if ($user) {
    //             $user->name?->delete();
    //             $user->address?->delete();
    //           //  $user->delete();
    //         }
    //     });
    // }



    //     public function getGroupedDeceasedNamesAttribute()
    // {
    //     return $this->contributions()
    //         ->where('status', '!=', 1) // Only unpaid contributions
    //         ->with('deceased.member.name')
    //         ->get()
    //         ->map(function ($contribution) {
    //             $user = optional($contribution->deceased?->member?->user?->name);
    //             if ($user) {
    //                 return "{$user->last_name}, {$user->first_name} {$user->middle_name}";
    //             }
    //             return null;
    //         })
    //         ->filter()
    //         ->unique()
    //         ->sort()
    //         ->implode(', ');
    // }


}
