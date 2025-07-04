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
        // Step 1: Create 3 Coordinators first
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
                'province' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'Philippines',
            ]);

            $coordinator = Member::create([
                'names_id' => $name->namesid,
                'address_id' => $address->addressid,
                'membership_date' => now(),
                'membership_status' => 0,
                'phone' => fake()->phoneNumber(),
                'role' => 'coordinator',
                'coordinator_id' => null, // A coordinator has no coordinator
                'image_photo' => null,
                'boligid' => '',
            ]);
            $coordinator->update(['boligid' => 'BOLIG-' . str_pad($coordinator->memberID, 6, '0', STR_PAD_LEFT)]);
            $coordinators->push($coordinator);
        }

        // Step 2: Create 10 regular members and assign to a random coordinator
        for ($i = 0; $i < 15; $i++) {
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
                'province' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'Philippines',
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
                'boligid' => '', // Temporary
            ]);
            $member->update(['boligid' => 'BOLIG-' . str_pad($member->memberID, 6, '0', STR_PAD_LEFT)]);
        }
    }
}
