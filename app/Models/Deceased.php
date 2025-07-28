<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Deceased extends Model
{
    protected $table = 'deceased';
    protected $primaryKey = 'deceasedID';

    protected $fillable = [
        'member_id',
        'month',
        'year',
        'date_of_death',
        'cause_of_death',
    ];
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'memberID');
    }


    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'deceased_id', 'deceasedID');
    }

    public function getFullNameAttribute()
    {
        return $this->member?->full_name ?? 'N/A';
    }



    protected static function booted()
    {
        static::creating(function ($deceased) {
            if ($deceased->date_of_death) {
                $date = Carbon::parse($deceased->date_of_death);
                $deceased->month = (int) $date->format('m');
                $deceased->year = $date->format('Y');
            }
        });
    }
    public function getTotalCollectedAmountAttribute(): float
    {
        return $this->contributions
            ->where('status', '1')
            ->where('release_status', '0')
            ->sum('amount');
    }
    public function getTotalReleasedAmountAttribute(): float
    {
        return $this->contributions
            ->where('status', '1')
            ->where('release_status', '1')
            ->sum('amount');
    }
}
