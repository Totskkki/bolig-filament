<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Users\Name;
use App\Models\Users\Address;
use App\Models\User;
use App\Models\Member;
use Illuminate\Support\Str;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            // Create Name
            $name = Name::create([
                'first_name' => fake()->firstName(),
                'middle_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'suffix' => fake()->optional()->suffix(),
                'gender' => fake()->randomElement(['Male', 'Female', 'Others']),
                'birthday' => fake()->date(),
                'age' => fake()->numberBetween(18, 60),
            ]);

            // Create Address
            $address = Address::create([
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'province' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'Philippines',
            ]);

            // Create User
            //  $user = User::create([
            // 'username' => null,
            //'email' => null,
            // 'password' => null, // or null if you want to skip
            // 'role' => 'member',
            // 'status' => 'active',
            //'contact_number' => fake()->phoneNumber(),
            //'photo' => null,
            // 'name_id' => $name->namesid,
            // 'address_id' => $address->addressid,
            // ]);

            // Create Member
            Member::create([
                // 'user_id' => $user->userid,
                'names_id' => $name->namesid,
                'address_id' => $address->addressid,
                'membership_date' => now(),
                'membership_status' => 'active',
                'phone' => fake()->phoneNumber(),
                'photo' => null,
            ]);
        }
    }
}
