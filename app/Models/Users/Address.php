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

    // 🔁 Static cached data
    protected static array $regionData = [];
    protected static array $provinceData = [];
    protected static array $cityData = [];
    protected static array $barangayData = [];

    // ✅ Load JSON once per request
    protected static function booted()
    {
        static::$regionData = json_decode(file_get_contents(storage_path('app/locations/region.json')), true);
        static::$provinceData = json_decode(file_get_contents(storage_path('app/locations/province.json')), true);
        static::$cityData = json_decode(file_get_contents(storage_path('app/locations/city.json')), true);
        static::$barangayData = json_decode(file_get_contents(storage_path('app/locations/barangay.json')), true);
    }

    // ✅ Use cached array
    public function getProvinceNameAttribute(): string
    {
        return collect(static::$provinceData)->firstWhere('province_code', $this->province)['province_name'] ?? 'Unknown Province';
    }

    public function getCityNameAttribute(): string
    {
        return collect(static::$cityData)->firstWhere('city_code', $this->city)['city_name'] ?? 'Unknown City';
    }

    public function getBarangayNameAttribute(): string
    {
        return collect(static::$barangayData)->firstWhere('brgy_code', $this->barangay)['brgy_name'] ?? 'Unknown Barangay';
    }

    public function getRegionNameAttribute(): string
    {
        return collect(static::$regionData)->firstWhere('region_code', $this->region)['region_name'] ?? 'Unknown Region';
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->street}, {$this->barangay_name}, {$this->city_name}, {$this->province_name}, {$this->region_name}";
    }

    public function users()
    {
        return $this->hasMany(User::class, 'address_id', 'addressid');
    }
}
