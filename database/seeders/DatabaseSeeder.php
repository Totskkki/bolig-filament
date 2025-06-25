<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            NameSeeder::class,
            AddressSeeder::class,
            UserSeeder::class,
            MemberSeeder::class,
            LocationSeeder::class,

        ]);
        \App\Models\SystemSetting::firstOrCreate(
            ['key' => 'mortuary_contribution'],
            ['value' => '15', 'description' => 'Per member contribution']
        );
        // User::create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@example.com',
        //     'password' => bcrypt('password'),
        //     'role' => 'admin',
        // ]);

        // User::create([
        //     'name' => 'Staff User',
        //     'email' => 'staff@example.com',
        //     'password' => bcrypt('password'),
        //     'role' => 'staff',
        // ]);
        // Member::create([
        //     'first_name' => 'Tots',
        //     'last_name' => 'Totsky',
        //     'birthdate' => '2001-12-01', // better format
        //     'age' => 23, // age should be an integer
        //     'address' => 'Koronadal City',
        //     'contact_number' => '09677819501',
        //     'status' => 'active',
        // ]);
    }
}
