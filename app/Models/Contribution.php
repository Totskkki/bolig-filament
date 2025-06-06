<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Contribution extends Model
{
    protected $table = 'contributions';

    protected $primaryKey = 'consid';
    protected $fillable = [
        'payer_memberID',
        'deceasedID',
        'amount',
        'adjusted_amount', // <-- Add this
        'status',
        'payment_date',
        'remarks',
    ];

    public function payer()
    {
        return $this->belongsTo(Member::class, 'payer_memberID', 'memberID');
    }

    public function deceased()
    {
        return $this->belongsTo(Deceased::class, 'deceasedID', 'deceasedID');
    }

  public static function groupByPayer(Builder $query): Builder
    {
        return $query
            ->selectRaw('
            payer_memberID,
            SUM(CASE WHEN status != 1 THEN amount ELSE 0 END) as amount,
            MAX(payment_date) as payment_date,
            MAX(status) as status,
            MAX(remarks) as remarks,
            MAX(consid) as consid
        ')
        ->groupBy('payer_memberID')
        ->havingRaw('SUM(CASE WHEN status != 1 THEN amount ELSE 0 END) > 0');
    }


}
