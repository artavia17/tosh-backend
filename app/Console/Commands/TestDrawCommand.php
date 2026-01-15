<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\DrawPeriod;
use Illuminate\Console\Command;

class TestDrawCommand extends Command
{
    protected $signature = 'test:draw';
    protected $description = 'Test draw execution for Guatemala';

    public function handle()
    {
        $guatemala = Country::where('iso_code', 'GT')->first();

        $periods = DrawPeriod::where('country_id', $guatemala->id)
            ->where('draw_executed', false)
            ->orderBy('start_date')
            ->get();

        $this->info('=== EJECUTANDO SORTEOS DE PRUEBA ===' . PHP_EOL);

        foreach ($periods as $index => $period) {
            $this->info("Ejecutando Sorteo " . ($index + 1) . ": {$period->name}");

            // Usar reflexión para acceder al método protegido
            $reflection = new \ReflectionClass(\App\Filament\Resources\DrawPeriodResource::class);
            $method = $reflection->getMethod('executeWeeklyDraw');
            $method->setAccessible(true);
            $method->invoke(null, $period);

            $this->info("  ✓ Completado\n");
        }

        $this->info('=== TODOS LOS SORTEOS EJECUTADOS ===');
    }
}
