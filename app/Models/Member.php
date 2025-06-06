<?php

namespace App\Models;

use App\Models\Contribution;
use App\Models\Deceased;
use App\Models\Users\Address;
use App\Models\Users\Name;
use Illuminate\Database\Eloquent\Model;


class Member extends Model
{
    protected $primaryKey = 'memberID';

    protected $fillable = [
       // 'user_id',
        'names_id',
        'address_id',
        'membership_date',
        'membership_status',
        'phone',
        'photo',
    ];

    // public function user()
    // {
    //     return $this->belongsTo(\App\Models\User::class, 'user_id', 'userid');
    // }

     public function name()
    {
        return $this->belongsTo(Name::class, 'names_id', 'namesid');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'addressid');
    }
    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'payer_memberID');
    }

    public function isDeceased()
    {
        return $this->deceased()->exists();
    }

    public function deceased()
    {
        return $this->hasMany(Deceased::class, 'memberID', 'memberID');
    }

    // public function getGroupedDeceasedNamesAttribute()
    // {
    //     return $this->contributions()
    //         ->with('deceased.member.user.name')
    //         ->get()
    //         ->map(function ($contribution) {
    //             $user = optional($contribution->deceased?->member?->user?->name);
    //             if ($user) {
    //                 return "{$user->last_name}, {$user->first_name} {$user->middle_name}";
    //             }
    //             return null;
    //         })
    //         ->filter() // remove nulls
    //         ->unique() // remove duplicates
    //         ->implode(', ');
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
}
