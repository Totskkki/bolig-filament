<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\SystemSetting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Database\Seeders\AddressSeeder;
use Database\Seeders\MemberSeeder;
use Database\Seeders\NameSeeder;
use Database\Seeders\UserSeeder;
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

        ]);
        SystemSetting::firstOrCreate(
            ['key' => 'mortuary_contribution'],
            ['value' => '15', 'description' => 'Monthly mortuary contribution in PHP']
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
