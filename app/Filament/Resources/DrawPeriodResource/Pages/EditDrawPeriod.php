<?php

namespace App\Filament\Resources\DrawPeriodResource\Pages;

use App\Filament\Resources\DrawPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDrawPeriod extends EditRecord
{
    protected static string $resource = DrawPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar la configuración de premios desde la tabla pivot
        $prizes = $this->record->prizes()->get();
        $prizeConfigs = [];

        foreach ($prizes as $prize) {
            $prizeConfigs[] = [
                'prize_id' => $prize->id,
                'max_quantity' => $prize->pivot->max_quantity,
            ];
        }

        $data['prizeConfigs'] = $prizeConfigs;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraer la configuración de premios antes de guardar
        $this->prizeConfigs = $data['prizeConfigs'] ?? [];
        unset($data['prizeConfigs']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Sincronizar los premios con la tabla pivot
        if (isset($this->prizeConfigs)) {
            $syncData = [];
            foreach ($this->prizeConfigs as $config) {
                if (isset($config['prize_id']) && isset($config['max_quantity'])) {
                    // Obtener la cantidad ya otorgada para mantenerla
                    $existingPrize = $this->record->prizes()->where('prize_id', $config['prize_id'])->first();
                    $awardedQuantity = $existingPrize ? $existingPrize->pivot->awarded_quantity : 0;

                    $syncData[$config['prize_id']] = [
                        'max_quantity' => $config['max_quantity'],
                        'awarded_quantity' => $awardedQuantity,
                    ];
                }
            }

            $this->record->prizes()->sync($syncData);
        }
    }

    protected array $prizeConfigs = [];
}
