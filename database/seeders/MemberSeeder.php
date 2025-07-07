<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\Name;
use App\Models\Users\Address;
use App\Models\Member;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $coordinators = collect();

        for ($i = 0; $i < 3; $i++) {
            $name = Name::create([
                'first_name' => fake()->firstName(),
                'middle_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'suffix' => fake()->optional()->suffix(),
                'gender' => fake()->randomElement(['Male', 'Female', 'Others']),
                'birthday' => fake()->date(),
                'age' => fake()->numberBetween(30, 60),
            ]);

            $address = Address::create([
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'barangay' => fake()->randomElement([
                    'Poblacion',
                    'San Jose',
                    'San Isidro',
                    'Barangay Uno',
                    'Zone 2',
                ]),

                'province' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'region' => '11',
            ]);

            $coordinator = Member::create([
                'names_id' => $name->namesid,
                'address_id' => $address->addressid,
                'membership_date' => now(),
                'membership_status' => 0,
                'phone' => fake()->phoneNumber(),
                'role' => 'coordinator',
                'coordinator_id' => null,
                'image_photo' => null,
                'boligid' => '', // will update below
            ]);

            $sequence = $this->getNextProvinceSequence($address->region);
            $boligid = $this->generateBoligId($address->region, $sequence, 'coordinator');

        $coordinator->update(['boligid' => $boligid]);
        $coordinators->push($coordinator);
        }

        for ($i = 0; $i < 10; $i++) {
            $name = Name::create([
                'first_name' => fake()->firstName(),
                'middle_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'suffix' => fake()->optional()->suffix(),
                'gender' => fake()->randomElement(['Male', 'Female', 'Others']),
                'birthday' => fake()->date(),
                'age' => fake()->numberBetween(18, 40),
            ]);

            $address = Address::create([
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'barangay' => fake()->randomElement([
                    'Poblacion',
                    'San Jose',
                    'San Isidro',
                    'Barangay Uno',
                    'Zone 2',
                ]),

                'province' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'region' => '12',
            ]);

            $member = Member::create([
                'names_id' => $name->namesid,
                'address_id' => $address->addressid,
                'membership_date' => now(),
                'membership_status' => 0,
                'phone' => fake()->phoneNumber(),
                'role' => 'member',
                'coordinator_id' => $coordinators->random()->memberID,
                'image_photo' => null,
                'boligid' => '',
            ]);

            $sequence = $this->getNextProvinceSequence($address->region);
        $boligid = $this->generateBoligId($address->region, $sequence, 'member');

        $member->update(['boligid' => $boligid]);
        }
    }



  private function generateBoligId(string $regionCode, string $type): string
{
    $suffix = $type === 'coordinator' ? '01' : '02';

    do {
        // Generate random 8-digit number
        $randomNumber = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $boligid = "{$regionCode}-{$randomNumber}-{$suffix}";

        // Check uniqueness
        $exists = Member::where('boligid', $boligid)->exists();
    } while ($exists);

    return $boligid;
}


private function getNextProvinceSequence(string $regionCode): int
{
    // Count how many members already exist for that province
    return Member::whereHas('address', function ($q) use ($regionCode) {
        $q->where('region', $regionCode);
    })->count() + 1;
}

}
