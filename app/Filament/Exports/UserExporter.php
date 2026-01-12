<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('email'),
            ExportColumn::make('email_verified_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('country.name'),
            ExportColumn::make('id_type'),
            ExportColumn::make('id_number'),
            ExportColumn::make('phone_number'),
            ExportColumn::make('marketing_opt_in'),
            ExportColumn::make('whatsapp_opt_in'),
            ExportColumn::make('phone_opt_in'),
            ExportColumn::make('email_opt_in'),
            ExportColumn::make('sms_opt_in'),
            ExportColumn::make('data_treatment_accepted'),
            ExportColumn::make('terms_accepted'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
