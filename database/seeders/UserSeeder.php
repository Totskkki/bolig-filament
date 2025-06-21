<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'), // NEVER store plain text
            'role' => 'admin', // NEVER store plain text
            'name_id' => 1,
            'address_id' => 1,
        ]);

        User::create([
            'username' => 'staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('staff'),
            'role' => 'staff',
            'name_id' => 2,
            'address_id' => 2,
        ]);
    }
}
