<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    protected $table = 'contributions';
    protected $appends = ['name'];

    protected $fillable = [
        'payer_memberID',
        'deceasedID',
        'amount',
        'adjusted_amount',
        'status',
        'payment_date',
        'month',
        'year',
        'remarks',
    ];

    // ✅ Link to member who pays
    public function payer()
    {
        return $this->belongsTo(Member::class, 'payer_memberID', 'id');
    }

    // ✅ Link to deceased
    public function deceased()
    {
        return $this->belongsTo(Deceased::class, 'deceasedID', 'id');
    }

    // ✅ Display full name of payer via related name model
    public function getNameAttribute()
    {
        $name = optional($this->payer?->name);

        return $name ? "{$name->last_name}, {$name->first_name} {$name->middle_name}" : null;
    }

    // ✅ Useful computed helpers
    public function getHasPaidAttribute()
    {
        return (bool) $this->status;
    }

    public function getStatusTextAttribute(): string
    {
        return $this->hasPaid ? 'Paid' : 'Unpaid';
    }


    public static function scopeGroupByPayer(Builder $query): Builder
    {
        return $query->where('status', false);
    }

}
