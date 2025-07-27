<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('barangays')->truncate();
        DB::table('cities')->truncate();
        DB::table('provinces')->truncate();
        DB::table('regions')->truncate();
        Schema::enableForeignKeyConstraints();

        $regions   = collect(json_decode(file_get_contents(storage_path('app/locations/region.json')), true));
        $provinces = collect(json_decode(file_get_contents(storage_path('app/locations/province.json')), true))->unique('province_code');
        $cities    = collect(json_decode(file_get_contents(storage_path('app/locations/city.json')), true));
        $barangays = collect(json_decode(file_get_contents(storage_path('app/locations/barangay.json')), true));

        // --- Seed Regions in batch
        $regionData = $regions->map(fn($r) => [
            'region_code' => $r['region_code'],
            'region_name' => $r['region_name'],
        ])->toArray();

        foreach (array_chunk($regionData, 500) as $chunk) {
            DB::table('regions')->insert($chunk);
        }

        $regionMap = DB::table('regions')->pluck('id', 'region_code');

        // --- Seed Provinces in batch
        $provinceData = $provinces->map(function ($p) use ($regionMap) {
            $regionId = $regionMap[$p['region_code']] ?? null;
            if ($regionId) {
                return [
                    'province_code' => $p['province_code'],
                    'province_name' => $p['province_name'],
                    'region_id'     => $regionId,
                ];
            }
            return null;
        })->filter()->values()->toArray();

        foreach (array_chunk($provinceData, 500) as $chunk) {
            DB::table('provinces')->insert($chunk);
        }

        $provinceMap = DB::table('provinces')->pluck('id', 'province_code');

        // --- Seed Cities in batch
        $cityData = $cities->map(function ($c) use ($provinceMap) {
            $provinceId = $provinceMap[$c['province_code']] ?? null;
            if ($provinceId) {
                return [
                    'city_code'    => $c['city_code'],
                    'city_name'    => $c['city_name'],
                    'province_id'  => $provinceId,
                ];
            }
            return null;
        })->filter()->values()->toArray();

        foreach (array_chunk($cityData, 500) as $chunk) {
            DB::table('cities')->insert($chunk);
        }

        $cityMap = DB::table('cities')->pluck('id', 'city_code');

        // --- Seed Barangays in batch
        $barangayData = $barangays->map(function ($b) use ($cityMap) {
            $cityId = $cityMap[$b['city_code']] ?? null;
            if ($cityId) {
                return [
                    'brgy_code' => $b['brgy_code'],
                    'brgy_name' => $b['brgy_name'],
                    'city_id'   => $cityId,
                ];
            }
            return null;
        })->filter()->values()->toArray();

        foreach (array_chunk($barangayData, 1000) as $chunk) {
            DB::table('barangays')->insert($chunk);
        }
    }
}
