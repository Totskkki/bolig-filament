<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\Address;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        Address::create([
            'street' => '123 Mabini St.',
            'city' => 'Quezon City',
            'barangay' => 'Quezon City',
            'province' => 'Metro Manila',
            'postal_code' => '1100',
            'region' => 'Region VI',
        ]);

        Address::create([
            'street' => '456 Luna Ave.',
            'city' => 'Cebu City',
             'barangay' => 'Quezon City',
            'province' => 'Cebu',
            'postal_code' => '6000',
            'region' => 'Region VI',
        ]);
    }
}
