<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\Name;

class NameSeeder extends Seeder
{
    public function run(): void
    {
        Name::create([
            'first_name' => 'Juan',
            'middle_name' => 'D.',
            'last_name' => 'Cruz',
            'gender' => 'male',
            'age' => '30',
            'birthday' => '2002-01-06',
        ]);

        Name::create([
            'first_name' => 'Maria',
            'middle_name' => 'S.',
            'last_name' => 'Lopez',
             'gender' => 'female',
             'age' => '25',
            'birthday' => '2001-02-25',
        ]);
    }
}
