<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\SystemSetting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Database\Seeders\AddressSeeder;
use Database\Seeders\LocationSeeder;
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
           // LocationSeeder::class,

        ]);
        SystemSetting::firstOrCreate(
            ['key' => 'mortuary_contribution'],
            ['value' => '15', 'description' => 'Per member contribution']
        );
       SystemSetting::firstOrCreate(
            ['key' => 'coordinator_share_percentage'],
            [
                'value' => 12,
                'description' => 'Coordinator share percentage per payment (e.g., 12%)',
            ]
        );
    }
}
