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
    protected $primaryKey = 'memberID';

    protected $fillable = [
        'names_id',
        'address_id',
        'membership_date',
        'membership_status',
        'phone',
        'image_photo',
        'role',
        'coordinator_id',
        'boligid',
    ];



    public function coordinator()
    {
        return $this->belongsTo(Member::class, 'coordinator_id');
    }

    public function name()
    {
        return $this->belongsTo(Name::class, 'names_id', 'namesid');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'addressid');
    }
    // public function contributions()
    // {
    //     return $this->hasMany(Contribution::class, 'payer_memberID');
    // }

    public function isDeceased()
    {
        return $this->deceased()->exists();
    }

    public function deceased()
    {
        return $this->hasMany(Deceased::class, 'memberID', 'member_id');
    }


    public function getFullNameAttribute(): string
    {
        return $this->name
            ? "{$this->name->first_name} {$this->name->middle_name} {$this->name->last_name}"
            : 'N/A';
    }



    // All members under this coordinator
    public function assignedMembers()
    {
        return $this->hasMany(Member::class, 'coordinator_id');
    }



    public function unpaidContributions()
    {
        return $this->hasMany(\App\Models\Contribution::class, 'payer_memberID', 'memberID')
            ->where('status', 0); // adjust if needed
    }


    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'payer_memberID');
    }


//     protected static function booted()
// {
//     static::deleting(function ($member) {
//         $member->name?->delete();
//         $member->address?->delete();
//     });

//     static::created(function ($member) {

//         do {
//             $uid = 'BOLIG-' . mt_rand(1000000000, 9999999999);
//         } while (self::where('boligid', $uid)->exists());

//         $member->updateQuietly([
//             'boligid' => $uid,
//         ]);
//     });
// }
protected static function booted()
{
    static::deleting(function ($member) {
        $member->name?->delete();
        $member->address?->delete();
    });

    static::created(function ($member) {
        $member->load('address');

        if (!$member->address || !$member->address->region) {
            return;
        }

        $region = $member->address->region; // should be just the number (e.g., '6')
        $suffix = $member->role === 'coordinator' ? '01' : '02';

        $sequence = Member::whereHas('address', function ($query) use ($region) {
            $query->where('region', $region);
        })
        ->where('role', $member->role)
        ->count();

        $sequenceNumber = str_pad($sequence + 1, 8, '0', STR_PAD_LEFT);

        $boligid = "{$region}-{$sequenceNumber}-{$suffix}";

        $member->updateQuietly([
            'boligid' => $boligid,
        ]);
    });
}


    public function getRouteKeyName(): string
    {
        return 'memberID';
    }
    // In Member.php model

}
