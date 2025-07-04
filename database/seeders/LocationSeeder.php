<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint issues during truncate
        Schema::disableForeignKeyConstraints();

        DB::table('barangays')->truncate();
        DB::table('cities')->truncate();
        DB::table('provinces')->truncate();
        DB::table('regions')->truncate();

        Schema::enableForeignKeyConstraints();

        // Load JSON files
        $regionPath = storage_path('app/locations/region.json');
        $provincePath = storage_path('app/locations/province.json');
        $cityPath = storage_path('app/locations/city.json');
        $barangayPath = storage_path('app/locations/barangay.json');

        $regions = collect(json_decode(file_get_contents($regionPath), true));
        $provinces = collect(json_decode(file_get_contents($provincePath), true))
            ->unique('province_code');
        $cities = collect(json_decode(file_get_contents($cityPath), true));
        $barangays = collect(json_decode(file_get_contents($barangayPath), true));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate all
        DB::table('barangays')->truncate();
        DB::table('cities')->truncate();
        DB::table('provinces')->truncate();
        DB::table('regions')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        // Seed Regions
        DB::table('regions')->insert(
            $regions->map(fn($r) => [
                'region_code' => $r['region_code'],
                'region_name' => $r['region_name'],
            ])->toArray()
        );

        // Seed Provinces


        collect($provinces)->chunk(500)->each(function ($chunk) {
            DB::table('provinces')->upsert(
                $chunk->map(fn($p) => [
                    'province_code' => $p['province_code'],
                    'province_name' => $p['province_name'],
                    'region_code' => $p['region_code'],
                ])->toArray(),
                ['province_code'], // unique key
                ['province_name', 'region_code'] // fields to update
            );
        });

        // Seed Cities
        DB::table('cities')->insert(
            $cities->map(fn($c) => [
                'city_code' => $c['city_code'],
                'city_name' => $c['city_name'],
                'province_code' => $c['province_code'],
            ])->toArray()
        );

        // Seed Barangays
        DB::table('barangays')->insert(
            $barangays->map(fn($b) => [
                'brgy_code' => $b['brgy_code'],
                'brgy_name' => $b['brgy_name'],
                'city_code' => $b['city_code'],
            ])->toArray()
        );
    }
}
