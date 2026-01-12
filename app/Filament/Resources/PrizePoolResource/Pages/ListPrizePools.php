<?php

namespace App\Filament\Resources\PrizePoolResource\Pages;

use App\Filament\Resources\PrizePoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrizePools extends ListRecords
{
    protected static string $resource = PrizePoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
