<?php

namespace Database\Seeders;

use App\Models\Code;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MassiveTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating MASSIVE test data for Guatemala...');

        // Obtener Guatemala
        $guatemala = Country::where('iso_code', 'GT')->first();

        if (!$guatemala) {
            $this->command->error('Guatemala not found. Please run country seeder first.');
            return;
        }

        $this->command->info("Country: {$guatemala->name}");

        // Crear 500 usuarios para Guatemala
        $usersToCreate = 500;
        $this->command->info("Creating {$usersToCreate} users...");

        $progressBar = $this->command->getOutput()->createProgressBar($usersToCreate);
        $progressBar->start();

        $totalCodes = 0;

        for ($i = 1; $i <= $usersToCreate; $i++) {
            $user = User::create([
                'name' => "Usuario GT Test {$i}",
                'email' => "usergt{$i}@test.com",
                'password' => Hash::make('password123'),
                'country_id' => $guatemala->id,
                'id_type' => 'passport',
                'id_number' => 'GT' . rand(100000, 999999),
                'phone_number' => '+502' . rand(10000000, 99999999),
                'marketing_opt_in' => true,
                'whatsapp_opt_in' => true,
                'phone_opt_in' => true,
                'email_opt_in' => true,
                'sms_opt_in' => true,
                'data_treatment_accepted' => true,
                'terms_accepted' => true,
            ]);

            // Crear entre 5 y 20 códigos por usuario
            $numCodes = rand(5, 20);

            for ($j = 1; $j <= $numCodes; $j++) {
                // Generar código único
                $code = 'GT-' . rand(100000, 999999) . '-' . rand(1000, 9999);

                // Crear código con fecha aleatoria en los últimos 30 días
                $randomDaysAgo = rand(1, 30);
                $randomDate = now()->subDays($randomDaysAgo);

                $codeModel = Code::create([
                    'user_id' => $user->id,
                    'code' => $code,
                ]);

                // Actualizar la fecha de creación manualmente
                $codeModel->created_at = $randomDate;
                $codeModel->save();

                $totalCodes++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();

        // Mostrar resumen
        $totalUsers = User::where('country_id', $guatemala->id)->count();
        $totalCodesDB = Code::whereHas('user', function ($query) use ($guatemala) {
            $query->where('country_id', $guatemala->id);
        })->count();

        $this->command->info('=================================');
        $this->command->info('MASSIVE test data created successfully!');
        $this->command->info("Country: Guatemala");
        $this->command->info("Total Users: {$totalUsers}");
        $this->command->info("Total Codes: {$totalCodesDB}");
        $this->command->info("Avg Codes per User: " . round($totalCodesDB / $totalUsers, 2));
        $this->command->info('=================================');
        $this->command->info('Ready to execute multiple draw periods!');
    }
}
