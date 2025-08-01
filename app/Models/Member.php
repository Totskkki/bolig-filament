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
    public function deceaseds()
    {
        return $this->hasMany(Deceased::class, 'member_id', 'memberID'); // adjust FK as needed
    }


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
    if (!$this->name) {
        return 'N/A';
    }

    $lastName = $this->name->last_name;
    $firstName = $this->name->first_name;
    $middleInitial = $this->name->middle_name ? strtoupper(substr($this->name->middle_name, 0, 1)) . '.' : '';

    return "{$lastName}, {$firstName} {$middleInitial}";
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

    // protected static function booted()
    // {
    //     static::deleting(function ($member) {
    //         $member->name?->delete();
    //         $member->address?->delete();
    //     });

    //     static::created(function ($member) {
    //         $member->load('address');

    //         if (!$member->address || !$member->address->region) {
    //             return;
    //         }

    //         $region = $member->address->region; // should be just the number (e.g., '6')
    //         $suffix = $member->role === 'coordinator' ? '01' : '02';

    //         $sequence = Member::whereHas('address', function ($query) use ($region) {
    //             $query->where('region', $region);
    //         })
    //             ->where('role', $member->role)
    //             ->count();

    //         $sequenceNumber = str_pad($sequence + 1, 8, '0', STR_PAD_LEFT);

    //         $boligid = "{$region}-{$sequenceNumber}-{$suffix}";

    //         $member->updateQuietly([
    //             'boligid' => $boligid,
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

            $region = $member->address->region; // numeric region
            $suffix = $member->role === 'coordinator' ? '01' : '02';

            do {
                // Generate random 8-digit number
                $randomNumber = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
                $boligid = "{$region}{$randomNumber}{$suffix}";

                // Check for uniqueness within the same region and role
                $exists = Member::where('boligid', $boligid)->exists();
            } while ($exists);

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
