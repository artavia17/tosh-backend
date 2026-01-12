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
            [
                'name' => 'México',
                'iso_code' => 'MX',
                'phone_code' => '52',
                'id_format' => 'xxxx-xxxxxx-xxx', // CURP format approx
                'phone_format' => 'xx-xxxx-xxxx',
                'phone_min_length' => 10,
                'phone_max_length' => 10,
                'id_min_length' => 18,
                'id_max_length' => 18,
            ],
            [
                'name' => 'Colombia',
                'iso_code' => 'CO',
                'phone_code' => '57',
                'id_format' => 'xx.xxx.xxx',
                'phone_format' => 'xxx-xxx-xxxx',
                'phone_min_length' => 10,
                'phone_max_length' => 10,
                'id_min_length' => 6,
                'id_max_length' => 10,
            ],
            [
                'name' => 'Argentina',
                'iso_code' => 'AR',
                'phone_code' => '54',
                'id_format' => 'xx.xxx.xxx',
                'phone_format' => 'x-xx-xxxx-xxxx',
                'phone_min_length' => 10,
                'phone_max_length' => 11,
                'id_min_length' => 7,
                'id_max_length' => 8,
            ],
            [
                'name' => 'Chile',
                'iso_code' => 'CL',
                'phone_code' => '56',
                'id_format' => 'xx.xxx.xxx-x',
                'phone_format' => 'x-xxxx-xxxx',
                'phone_min_length' => 9,
                'phone_max_length' => 9,
                'id_min_length' => 8,
                'id_max_length' => 9,
            ],
            [
                'name' => 'Perú',
                'iso_code' => 'PE',
                'phone_code' => '51',
                'id_format' => 'xxxxxxxx',
                'phone_format' => 'xxx-xxx-xxx',
                'phone_min_length' => 9,
                'phone_max_length' => 9,
                'id_min_length' => 8,
                'id_max_length' => 8,
            ],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
