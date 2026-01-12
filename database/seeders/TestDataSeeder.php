<?php

namespace Database\Seeders;

use App\Models\Code;
use App\Models\Country;
use App\Models\DrawPeriod;
use App\Models\Prize;
use App\Models\PrizePool;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test data...');

        // Obtener países de CAM
        $countries = Country::whereIn('iso_code', ['CR', 'GT', 'SV', 'NI', 'HN'])->get();

        if ($countries->isEmpty()) {
            $this->command->error('No countries found. Please run country seeder first.');
            return;
        }

        // Crear premios si no existen
        $prizes = [
            ['name' => 'Mochila Premium', 'description' => 'Mochila de alta calidad', 'is_active' => true],
            ['name' => 'Lonchera Térmica', 'description' => 'Lonchera con aislamiento térmico', 'is_active' => true],
            ['name' => 'Estuche Escolar', 'description' => 'Estuche completo con útiles', 'is_active' => true],
        ];

        foreach ($prizes as $prizeData) {
            Prize::firstOrCreate(['name' => $prizeData['name']], $prizeData);
        }

        $allPrizes = Prize::where('is_active', true)->get();
        $this->command->info('Prizes created: ' . $allPrizes->count());

        // Crear inventario de premios para cada país
        foreach ($countries as $country) {
            foreach ($allPrizes as $prize) {
                PrizePool::updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'prize_id' => $prize->id,
                    ],
                    [
                        'total_quantity' => 100,
                        'awarded_quantity' => 0,
                        'weekly_target' => 10,
                    ]
                );
            }
            $this->command->info("Prize pools created for {$country->name}");
        }

        // Crear usuarios y códigos para cada país
        foreach ($countries as $country) {
            $this->command->info("Creating users and codes for {$country->name}...");

            for ($i = 1; $i <= 50; $i++) {
                $user = User::create([
                    'name' => "Usuario Test {$country->iso_code} {$i}",
                    'email' => "user{$i}_{$country->iso_code}@test.com",
                    'password' => Hash::make('password123'),
                    'country_id' => $country->id,
                    'id_type' => 'passport',
                    'id_number' => $country->iso_code . rand(100000, 999999),
                    'phone_number' => '+506' . rand(10000000, 99999999),
                    'marketing_opt_in' => true,
                    'whatsapp_opt_in' => true,
                    'phone_opt_in' => true,
                    'email_opt_in' => true,
                    'sms_opt_in' => true,
                    'data_treatment_accepted' => true,
                    'terms_accepted' => true,
                ]);

                // Crear entre 1 y 5 códigos por usuario
                $numCodes = rand(1, 5);
                for ($j = 1; $j <= $numCodes; $j++) {
                    // Generar código único
                    $code = strtoupper($country->iso_code) . '-' . rand(100000, 999999) . '-' . rand(1000, 9999);

                    // Crear código con fecha aleatoria en el rango del período de sorteo
                    // Usar fechas dentro del período (2025-12-29 a 2026-01-04)
                    $randomDate = now()->subDays(rand(1, 7));

                    $codeModel = Code::create([
                        'user_id' => $user->id,
                        'code' => $code,
                    ]);

                    // Actualizar la fecha de creación manualmente
                    $codeModel->created_at = $randomDate;
                    $codeModel->save();
                }
            }

            $this->command->info("Created 50 users with multiple codes for {$country->name}");
        }

        // Mostrar resumen
        $totalUsers = User::count();
        $totalCodes = Code::count();
        $totalPrizes = Prize::count();
        $totalPrizePools = PrizePool::count();

        $this->command->info('=================================');
        $this->command->info('Test data created successfully!');
        $this->command->info("Total Users: {$totalUsers}");
        $this->command->info("Total Codes: {$totalCodes}");
        $this->command->info("Total Prizes: {$totalPrizes}");
        $this->command->info("Total Prize Pools: {$totalPrizePools}");
        $this->command->info('=================================');
        $this->command->info('Now you can configure a Draw Period and execute the draw!');
    }
}
