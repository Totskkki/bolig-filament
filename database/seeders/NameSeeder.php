<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\Name;

class NameSeeder extends Seeder
{
    public function run(): void
    {
        Name::create([
            'first_name' => 'Admin',
            'middle_name' => '',
            'last_name' => 'Admin',
            'gender' => 'male',
            'age' => '30',
            'birthday' => '2002-01-06',
        ]);

        Name::create([
            'first_name' => 'Staff',
            'middle_name' => '',
            'last_name' => 'Staff',
             'gender' => 'female',
             'age' => '25',
            'birthday' => '2001-02-25',
        ]);
    }
}
