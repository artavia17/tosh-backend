<?php

namespace App\Filament\Exports;

use App\Models\Winner;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class WinnerExporter extends Exporter
{
    protected static ?string $model = Winner::class;

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Exportación completada: ' . number_format($export->successful_rows) . ' ' . str('ganador')->plural($export->successful_rows) . ' exportados.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' no se pudieron exportar.';
        }

        return $body;
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('drawPeriod.name')
                ->label('Período de Sorteo'),
            ExportColumn::make('drawPeriod.start_date')
                ->label('Fecha Inicio'),
            ExportColumn::make('drawPeriod.end_date')
                ->label('Fecha Final'),
            ExportColumn::make('country.name')
                ->label('País'),
            ExportColumn::make('user.name')
                ->label('Nombre del Ganador'),
            ExportColumn::make('user.email')
                ->label('Email'),
            ExportColumn::make('user.phone_number')
                ->label('Teléfono'),
            ExportColumn::make('user.id_number')
                ->label('Identificación'),
            ExportColumn::make('code.code')
                ->label('Código Ganador'),
            ExportColumn::make('prize.name')
                ->label('Premio'),
            ExportColumn::make('created_at')
                ->label('Fecha de Asignación')
                ->formatStateUsing(fn ($state) => $state ? $state->format('Y-m-d H:i:s') : ''),
        ];
    }
}
