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



    protected static function booted()
    {
        static::creating(function ($deceased) {
            if ($deceased->date_of_death) {
                $date = Carbon::parse($deceased->date_of_death);
                $deceased->month = $date->format('m'); // or 'F' for full month name
                $deceased->year = $date->format('Y');
            }
        });
    }
}
