<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HmoSeeder extends Seeder
{
    private $hmos = [
        ['name'=>'HMO A', 'code'=> 'HMO-A'],
        ['name'=>'HMO B', 'code'=> 'HMO-B'],
        ['name'=>'HMO C', 'code'=> 'HMO-C'],
        ['name'=>'HMO D', 'code'=> 'HMO-D'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $hmosWithEmails = array_map(function($hmo) use ($faker) {
            return [
                'name' => $hmo['name'],
                'code' => $hmo['code'],
                'email' => $faker->unique()->safeEmail, // Generate a random unique email
            ];
        }, $this->hmos);

        DB::table('hmos')->insert($hmosWithEmails);
    }
}
