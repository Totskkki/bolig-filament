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

    }
}
