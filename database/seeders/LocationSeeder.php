<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $countries = collect(json_decode(file_get_contents(storage_path('app/locations/countries.json')), true));
        $provinces = collect(json_decode(file_get_contents(storage_path('app/locations/states.json')), true));
        $cities = collect(json_decode(file_get_contents(storage_path('app/locations/cities.json')), true));

        DB::table('countries')->insert($countries->map(fn($c) => [
            'id' => $c['id'],
            'name' => $c['name'],
        ])->toArray());

        DB::table('provinces')->insert($provinces->map(fn($p) => [
            'id' => $p['id'],
            'name' => $p['name'],
            'country_id' => $p['country_id'],
        ])->toArray());

        $cities->chunk(1000)->each(function ($chunk) {
            DB::table('cities')->insert($chunk->map(fn($c) => [
                'id' => $c['id'],
                'name' => $c['name'],
                'province_id' => $c['state_id'],
            ])->toArray());
        });
    }
}
