<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deceased extends Model
{
    protected $table = 'deceased';
    protected $primaryKey = 'deceasedID';

    protected $fillable = [
        'memberID',
        'date_of_death',
        'cause_of_death',
    ];
    public function member()
    {
        return $this->belongsTo(Member::class, 'memberID');
    }

    public function deceasedMember()
    {
        return $this->belongsTo(Member::class, 'memberID');
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'deceasedID');
    }
 
    
}
