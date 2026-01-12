<?php

namespace App\Filament\Resources\WinnerResource\Pages;

use App\Filament\Resources\WinnerResource;
use App\Models\Country;
use App\Models\User;
use App\Models\Winner;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListWinners extends ListRecords
{
    protected static string $resource = WinnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Los ganadores ahora se generan automáticamente desde "Períodos de Sorteo"
        ];
    }
}
