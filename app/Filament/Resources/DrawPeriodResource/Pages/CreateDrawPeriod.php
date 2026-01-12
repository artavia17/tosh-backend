<?php

namespace App\Filament\Resources\DrawPeriodResource\Pages;

use App\Filament\Resources\DrawPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDrawPeriod extends CreateRecord
{
    protected static string $resource = DrawPeriodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraer la configuración de premios antes de crear el registro
        $this->prizeConfigs = $data['prizeConfigs'] ?? [];
        unset($data['prizeConfigs']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sincronizar los premios con la tabla pivot
        if (!empty($this->prizeConfigs)) {
            $syncData = [];
            foreach ($this->prizeConfigs as $config) {
                if (isset($config['prize_id']) && isset($config['max_quantity'])) {
                    $syncData[$config['prize_id']] = [
                        'max_quantity' => $config['max_quantity'],
                        'awarded_quantity' => 0,
                    ];
                }
            }

            if (!empty($syncData)) {
                $this->record->prizes()->sync($syncData);
            }
        }
    }

    protected array $prizeConfigs = [];
}
