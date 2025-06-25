<?php

namespace App\Models\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';
    protected $primaryKey = 'addressid';
    // protected static $countryList;
    // protected static $provinceList;
    // protected static $cityList;

    // protected static $countryMap = null;
    // protected static $provinceMap = null;
    // protected static $cityMap = null;

    protected $fillable = [
        'street',
        'city',
        'province',
        'country',
        'postal_code',
        'country',

    ];

    public function users()
    {
        return $this->hasMany(User::class, 'address_id', 'addressid');
    }

    public function getCityNameAttribute(): string
    {
        return DB::table('cities')->where('id', $this->city)->value('name') ?? 'Unknown City';
    }

    public function getProvinceNameAttribute(): string
    {
        return DB::table('provinces')->where('id', $this->province)->value('name') ?? 'Unknown Province';
    }

    public function getCountryNameAttribute(): string
    {
        return DB::table('countries')->where('id', $this->country)->value('name') ?? 'Unknown Country';
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->street}, {$this->city_name}, {$this->province_name}, {$this->country_name}";
    }
}
