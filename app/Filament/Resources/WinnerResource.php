<?php

namespace App\Filament\Resources;

use App\Filament\Exports\WinnerExporter;
use App\Filament\Resources\WinnerResource\Pages;
use App\Filament\Resources\WinnerResource\RelationManagers;
use App\Models\Winner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WinnerResource extends Resource
{
    protected static ?string $model = Winner::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Ganadores';

    protected static ?string $modelLabel = 'Ganador';

    protected static ?string $pluralModelLabel = 'Ganadores';

    protected static ?string $navigationGroup = 'Gestión de Premios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('info')
                    ->label('Información')
                    ->content('Los ganadores se generan automáticamente desde los Períodos de Sorteo')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('drawPeriod.name')
                    ->label('Período')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('País')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code.code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prize.name')
                    ->label('Premio')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Asignación')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->label('País')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('prize_id')
                    ->relationship('prize', 'name')
                    ->label('Premio')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('draw_period_id')
                    ->relationship('drawPeriod', 'name')
                    ->label('Período de Sorteo')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\DeleteAction::make()->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Exportar a Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->modalHeading('Exportar Ganadores')
                    ->modalDescription('Seleccione el país y período para exportar')
                    ->form([
                        Forms\Components\Select::make('country_id')
                            ->label('País')
                            ->options(function () {
                                return \App\Models\Country::orderBy('name')->pluck('name', 'id')->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('draw_period_id', null)),
                        Forms\Components\Select::make('draw_period_id')
                            ->label('Período de Sorteo')
                            ->options(function (Forms\Get $get) {
                                $countryId = $get('country_id');
                                if (!$countryId) {
                                    return [];
                                }
                                return \App\Models\DrawPeriod::where('country_id', $countryId)
                                    ->where('draw_executed', true)
                                    ->orderBy('start_date', 'desc')
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (Forms\Get $get) => !$get('country_id'))
                            ->helperText('Primero seleccione un país'),
                    ])
                    ->action(function (array $data) {
                        if (empty($data['country_id']) || empty($data['draw_period_id'])) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Debe seleccionar un país y un período de sorteo')
                                ->send();
                            return;
                        }

                        $winners = \App\Models\Winner::query()
                            ->with(['drawPeriod', 'country', 'user', 'code', 'prize'])
                            ->where('country_id', $data['country_id'])
                            ->where('draw_period_id', $data['draw_period_id'])
                            ->get();

                        if ($winners->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Sin datos')
                                ->body('No hay ganadores para exportar con los filtros seleccionados.')
                                ->send();
                            return;
                        }

                        $period = \App\Models\DrawPeriod::find($data['draw_period_id']);
                        $fileName = 'ganadores_' . str_replace(' ', '_', $period->name) . '_' . now()->format('Y-m-d') . '.xlsx';

                        return response()->streamDownload(function () use ($winners) {
                            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                            $sheet = $spreadsheet->getActiveSheet();

                            // Headers
                            $headers = ['Período', 'Fecha Inicio', 'Fecha Final', 'País', 'Nombre', 'Email', 'Teléfono', 'Identificación', 'Código', 'Premio', 'Fecha Asignación'];
                            $sheet->fromArray($headers, null, 'A1');

                            // Data
                            $row = 2;
                            foreach ($winners as $winner) {
                                $sheet->fromArray([
                                    $winner->drawPeriod->name ?? '',
                                    $winner->drawPeriod->start_date->format('Y-m-d') ?? '',
                                    $winner->drawPeriod->end_date->format('Y-m-d') ?? '',
                                    $winner->country->name ?? '',
                                    $winner->user->name ?? '',
                                    $winner->user->email ?? '',
                                    $winner->user->phone_number ?? '',
                                    $winner->user->id_number ?? '',
                                    $winner->code->code ?? '',
                                    $winner->prize->name ?? '',
                                    $winner->created_at->format('Y-m-d H:i:s'),
                                ], null, 'A' . $row);
                                $row++;
                            }

                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                            $writer->save('php://output');
                        }, $fileName);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWinners::route('/'),
        ];
    }
}
