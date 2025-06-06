<?php

namespace App\Models\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses';
    protected $primaryKey = 'addressid';

    protected $fillable = [
        'street',
        'city',
        'province',
        'postal_code',
        'country',

    ];

    public function users()
    {
        return $this->hasMany(User::class, 'address_id', 'addressid');
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'userID'); // only if addresses.userID exists
    // }


    public function getCountryNameAttribute()
    {
        $countries = collect(json_decode(file_get_contents(storage_path('app/locations/countries.json')), true));
        $country = $countries->firstWhere('id', $this->country);
        return $country['name'] ?? 'Unknown';
    }

    public function getProvinceNameAttribute()
    {
        $states = collect(json_decode(file_get_contents(storage_path('app/locations/states.json')), true));
        $state = $states->firstWhere('id', $this->province);
        return $state['name'] ?? 'Unknown';
    }

    public function getCityNameAttribute()
    {
        $cities = collect(json_decode(file_get_contents(storage_path('app/locations/cities.json')), true));
        $city = $cities->firstWhere('id', $this->city);
        return $city['name'] ?? 'Unknown';
    }
    public function getFullAddressAttribute()
    {
        $countries = collect(json_decode(file_get_contents(storage_path('app/locations/countries.json')), true));
        $states = collect(json_decode(file_get_contents(storage_path('app/locations/states.json')), true));
        $cities = collect(json_decode(file_get_contents(storage_path('app/locations/cities.json')), true));

        $countryName = optional($countries->firstWhere('id', $this->country))['name'] ?? 'Unknown';
        $provinceName = optional($states->firstWhere('id', $this->province))['name'] ?? 'Unknown';
        $cityName = optional($cities->firstWhere('id', $this->city))['name'] ?? 'Unknown';

        return "{$countryName}, {$provinceName}, {$cityName}";
    }
    
}
