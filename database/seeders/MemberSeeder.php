<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberSeeder extends Seeder
{
    private array $generatedBoligIds = [];
    private array $barangayCache = [];
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Store created coordinators to assign randomly
        $coordinators = collect();
        $regionCoordinator = '11';
        $regionMember = '12';

        // Create Coordinators
        $coordinatorData = [];

        for ($i = 0; $i < 5; $i++) {
            $nameID = DB::table('names')->insertGetId([
                'first_name' => $faker->firstName(),
                'middle_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'suffix' => $faker->optional()->suffix(),
                'gender' => $faker->randomElement(['Male', 'Female', 'Others']),
                'birthday' => $faker->date(),
                'age' => $faker->numberBetween(30, 60),
            ]);

            $addressID = DB::table('addresses')->insertGetId([
                'street' => $faker->streetAddress(),
                'city' => $faker->city(),
                'barangay' => $faker->randomElement(['Poblacion', 'San Jose', 'San Isidro', 'Barangay Uno', 'Zone 2']),
                'province' => $faker->state(),
                'postal_code' => $faker->postcode(),
                'region' => $regionCoordinator,
            ]);

            $boligid = $this->generateBoligId($regionCoordinator, 'coordinator');

            $memberID = DB::table('members')->insertGetId([
                'names_id' => $nameID,
                'address_id' => $addressID,
                'membership_date' => now(),
                'membership_status' => 0,
                'phone' => $faker->phoneNumber(),
                'role' => 'coordinator',
                'coordinator_id' => null,
                'image_photo' => null,
                'boligid' => $boligid,
            ]);

            $coordinators->push($memberID);
        }

        // Batch member creation
        $memberChunks = [];
        $batchSize = 1000;

        for ($i = 0; $i < 5000; $i++) {
            $nameID = DB::table('names')->insertGetId([
                'first_name' => $faker->firstName(),
                'middle_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'suffix' => $faker->optional()->suffix(),
                'gender' => $faker->randomElement(['Male', 'Female', 'Others']),
                'birthday' => $faker->date(),
                'age' => $faker->numberBetween(18, 40),
            ]);

            $addressID = DB::table('addresses')->insertGetId([
                'street' => $faker->streetAddress(),
                'city' => $faker->city(),
                'barangay' => $faker->randomElement(['Poblacion', 'San Jose', 'San Isidro', 'Barangay Uno', 'Zone 2']),
                'province' => $faker->state(),
                'postal_code' => $faker->postcode(),
                'region' => $regionMember,
            ]);

            $boligid = $this->generateBoligId($regionMember, 'member');

            $memberChunks[] = [
                'names_id' => $nameID,
                'address_id' => $addressID,
                'membership_date' => now(),
                'membership_status' => 0,
                'phone' => $faker->phoneNumber(),
                'role' => 'member',
                'coordinator_id' => $coordinators->random(),
                'image_photo' => null,
                'boligid' => $boligid,
            ];

            // Insert in chunks
            if (count($memberChunks) >= $batchSize) {
                DB::table('members')->insert($memberChunks);
                $memberChunks = [];
            }
        }

        // Insert any remaining members
        if (!empty($memberChunks)) {
            DB::table('members')->insert($memberChunks);
        }
    }

    // private function generateBoligId(string $regionCode, string $type): string
    // {
    //     // This skips DB uniqueness check for performance
    //     $suffix = $type === 'coordinator' ? '01' : '02';

    //     return "{$regionCode}-" . str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT) . "-{$suffix}";
    // }
    private function generateBoligId(string $regionCode, string $type): string
{
    $suffix = $type === 'coordinator' ? '01' : '02';

    do {
        $randomNumber = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $boligid = "{$regionCode}-{$randomNumber}-{$suffix}";
    } while (
        in_array($boligid, $this->generatedBoligIds) ||
        DB::table('members')->where('boligid', $boligid)->exists()
    );

    $this->generatedBoligIds[] = $boligid;
    return $boligid;
}
private function generateRealAddress(): array
{
    $barangay = DB::table('barangays')
        ->inRandomOrder()
        ->first();

    $city = DB::table('cities')->where('id', $barangay->city_id)->first();
    $province = DB::table('provinces')->where('id', $city->province_id)->first();
    $region = DB::table('regions')->where('id', $province->region_id)->first();

    return [
        'street' => fake()->streetAddress(),
        'barangay' => $barangay->brgy_code,
        'city' => $city->city_code,
        'province' => $province->province_code,
        'region' => $region->region_code,
        'postal_code' => fake()->postcode(),
    ];
}
private function getRandomBarangayFromCache()
{
    if (empty($this->barangayCache)) {
        $this->barangayCache = DB::table('barangays')->get()->toArray();
    }
    return $this->barangayCache[array_rand($this->barangayCache)];
}

}
