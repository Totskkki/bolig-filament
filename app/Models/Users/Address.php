<?php
namespace App\Models\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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

    // ✅ Use DB to get names instead of loading JSON

    public function getRegionNameAttribute(): string
    {
        return Cache::rememberForever("region_{$this->region}", function () {
            return DB::table('regions')
                ->where('region_code', $this->region)
                ->value('region_name') ?? 'Unknown Region';
        });
    }

    public function getProvinceNameAttribute(): string
    {
        return Cache::rememberForever("province_{$this->province}", function () {
            return DB::table('provinces')
                ->where('province_code', $this->province)
                ->value('province_name') ?? 'Unknown Province';
        });
    }

    public function getCityNameAttribute(): string
    {
        return Cache::rememberForever("city_{$this->city}", function () {
            return DB::table('cities')
                ->where('city_code', $this->city)
                ->value('city_name') ?? 'Unknown City';
        });
    }

    public function getBarangayNameAttribute(): string
    {
        return Cache::rememberForever("barangay_{$this->barangay}", function () {
            return DB::table('barangays')
                ->where('brgy_code', $this->barangay)
                ->value('brgy_name') ?? 'Unknown Barangay';
        });
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
