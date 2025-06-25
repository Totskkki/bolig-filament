<?php

namespace App\Models\Users;

use App\Models\Member;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Name extends Model
{
    use HasFactory;

    protected $table = 'names';

    protected $primaryKey = 'namesid';



    protected $fillable = [

        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birthday',
        'age',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'name_id', 'namesid');
    }
     public function members()
    {
        return $this->hasMany(Member::class, 'memberID', 'memberID');
    }
    // In your Name model
    public function getFullNameAttribute()
    {
        return "{$this->last_name}, {$this->first_name} {$this->middle_name}";
    }
}
