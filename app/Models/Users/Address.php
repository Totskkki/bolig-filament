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

    protected $fillable = [
        'street',
        'barangay',
        'city',
        'province',
        'region',
        'postal_code',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'address_id', 'addressid');
    }

    public function getProvinceNameAttribute(): string
    {
        $provinces = collect(json_decode(file_get_contents(storage_path('app/locations/province.json')), true));
        return $provinces->firstWhere('province_code', $this->province)['province_name'] ?? 'Unknown Province';
    }

    public function getCityNameAttribute(): string
    {
        $cities = collect(json_decode(file_get_contents(storage_path('app/locations/city.json')), true));
        return $cities->firstWhere('city_code', $this->city)['city_name'] ?? 'Unknown City';
    }

    public function getBarangayNameAttribute(): string
    {
        $barangays = collect(json_decode(file_get_contents(storage_path('app/locations/barangay.json')), true));
        return $barangays->firstWhere('brgy_code', $this->barangay)['brgy_name'] ?? 'Unknown Barangay';
    }

    public function getRegionNameAttribute(): string
    {
        $regions = collect(json_decode(file_get_contents(storage_path('app/locations/region.json')), true));
        return $regions->firstWhere('region_code', $this->region)['region_name'] ?? 'Unknown Region';
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->street}, {$this->barangay_name}, {$this->city_name}, {$this->province_name}, {$this->region_name}";
    }
}
