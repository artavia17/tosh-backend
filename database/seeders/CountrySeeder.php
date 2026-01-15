<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Guatemala',
                'iso_code' => 'GT',
                'phone_code' => '502',
                'id_format' => 'xxxx-xxxxx-xxxx', // DPI format
                'phone_format' => 'xxxx-xxxx',
                'phone_min_length' => 8,
                'phone_max_length' => 8,
                'id_min_length' => 13,
                'id_max_length' => 13,
            ],
            [
                'name' => 'El Salvador',
                'iso_code' => 'SV',
                'phone_code' => '503',
                'id_format' => 'xxxxxxxx-x', // DUI format
                'phone_format' => 'xxxx-xxxx',
                'phone_min_length' => 8,
                'phone_max_length' => 8,
                'id_min_length' => 9,
                'id_max_length' => 9,
            ],
            [
                'name' => 'Costa Rica',
                'iso_code' => 'CR',
                'phone_code' => '506',
                'id_format' => 'x-xxxx-xxxx',
                'phone_format' => 'xxxx-xxxx',
                'phone_min_length' => 8,
                'phone_max_length' => 8,
                'id_min_length' => 9,
                'id_max_length' => 9,
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['iso_code' => $country['iso_code']],
                $country
            );
        }
    }
}
