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
        // 'user_id',
        'names_id',
        'address_id',
        'membership_date',
        'membership_status',
        'phone',
        'image_photo',
    ];


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
        return $this->hasMany(Deceased::class, 'memberID', 'member_id');
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
}
