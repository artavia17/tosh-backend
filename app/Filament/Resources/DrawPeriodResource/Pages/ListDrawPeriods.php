<?php

namespace App\Filament\Resources\DrawPeriodResource\Pages;

use App\Filament\Resources\DrawPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrawPeriods extends ListRecords
{
    protected static string $resource = DrawPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
