<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoordinatorEarning extends Model
{
    protected $fillable = ['contribution_id', 'coordinator_id', 'share_amount'];

    public function coordinator()
    {
        return $this->belongsTo(Member::class, 'coordinator_id');
    }

    public function contribution()
    {
        return $this->belongsTo(Contribution::class, 'contribution_id', 'consid');
    }


public function deceased()
{
    return $this->contribution->deceased ?? null;
}
}
