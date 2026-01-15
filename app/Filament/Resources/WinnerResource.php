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
                Tables\Actions\Action::make('assignWinner')
                    ->label('Asignar Ganador Manualmente')
                    ->color('primary')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Asignar Ganador Manualmente')
                    ->modalDescription('Asigne un ganador a un sorteo existente respetando las reglas de la campaña')
                    ->modalWidth('2xl')
                    ->form([
                        Forms\Components\Select::make('draw_period_id')
                            ->label('Período de Sorteo')
                            ->options(function () {
                                return \App\Models\DrawPeriod::where('draw_executed', true)
                                    ->with('country')
                                    ->orderBy('start_date', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($period) {
                                        return [$period->id => $period->name . ' (' . $period->country->name . ')'];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('code_id', null);
                                $set('prize_id', null);
                            }),
                        Forms\Components\Select::make('code_id')
                            ->label('Código del Usuario')
                            ->options(function (Forms\Get $get) {
                                $periodId = $get('draw_period_id');
                                if (!$periodId) {
                                    return [];
                                }

                                $period = \App\Models\DrawPeriod::find($periodId);
                                if (!$period) {
                                    return [];
                                }

                                // Obtener TODOS los códigos del período (sin restricciones)
                                return \App\Models\Code::with('user')
                                    ->whereHas('user', function ($query) use ($period) {
                                        $query->where('country_id', $period->country_id);
                                    })
                                    ->whereBetween('created_at', [$period->start_date, $period->end_date->addDay()])
                                    ->get()
                                    ->mapWithKeys(function ($code) {
                                        return [$code->id => $code->code . ' - ' . $code->user->name . ' (' . $code->user->email . ')'];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (Forms\Get $get) => !$get('draw_period_id'))
                            ->helperText('Se muestran todos los códigos del período seleccionado')
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('prize_id', null)),
                        Forms\Components\Select::make('prize_id')
                            ->label('Premio a Asignar')
                            ->options(function (Forms\Get $get) {
                                $periodId = $get('draw_period_id');
                                $codeId = $get('code_id');

                                if (!$periodId) {
                                    return [];
                                }

                                $period = \App\Models\DrawPeriod::find($periodId);

                                if (!$period) {
                                    return [];
                                }

                                // Obtener todos los premios del período
                                $periodPrizes = $period->prizes()->get();

                                // Obtener inventario de premios para mostrar información
                                $prizePools = \App\Models\PrizePool::where('country_id', $period->country_id)
                                    ->get()
                                    ->keyBy('prize_id');

                                // Si hay un código seleccionado, filtrar premios ya ganados por ese usuario NUNCA (en toda la campaña)
                                if ($codeId) {
                                    $code = \App\Models\Code::find($codeId);
                                    if ($code) {
                                        // Obtener premios que este usuario YA ganó ALGUNA VEZ (en cualquier sorteo)
                                        $userWonPrizesEver = \App\Models\Winner::where('user_id', $code->user_id)
                                            ->where('country_id', $period->country_id)
                                            ->pluck('prize_id')
                                            ->unique()
                                            ->toArray();

                                        // Filtrar premios que NO ha ganado NUNCA
                                        $periodPrizes = $periodPrizes->filter(function ($prize) use ($userWonPrizesEver) {
                                            return !in_array($prize->id, $userWonPrizesEver);
                                        });
                                    }
                                }

                                if ($periodPrizes->isEmpty()) {
                                    return ['disabled' => 'Este usuario ya ganó todos los premios disponibles (inhabilitado)'];
                                }

                                return $periodPrizes->mapWithKeys(function ($prize) use ($prizePools) {
                                    $pool = $prizePools->get($prize->id);
                                    return [
                                        $prize->id => $prize->name .
                                            ' (Período: ' . $prize->pivot->awarded_quantity . '/' . $prize->pivot->max_quantity .
                                            ', Stock: ' . ($pool ? $pool->remaining : 0) . ')'
                                    ];
                                })->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (Forms\Get $get) => !$get('draw_period_id'))
                            ->helperText('Solo se muestran premios que el usuario NO ha ganado nunca'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas (Opcional)')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        $period = \App\Models\DrawPeriod::find($data['draw_period_id']);
                        $code = \App\Models\Code::find($data['code_id']);
                        $prize = \App\Models\Prize::find($data['prize_id']);

                        // SIN validaciones - asignación manual permite duplicados

                        // Crear ganador
                        \App\Models\Winner::create([
                            'user_id' => $code->user_id,
                            'code_id' => $code->id,
                            'prize_id' => $prize->id,
                            'draw_period_id' => $period->id,
                            'country_id' => $period->country_id,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // Actualizar contador del período
                        $period->prizes()->updateExistingPivot($prize->id, [
                            'awarded_quantity' => \DB::raw('awarded_quantity + 1')
                        ]);

                        // Actualizar inventario general
                        \App\Models\PrizePool::where('prize_id', $prize->id)
                            ->where('country_id', $period->country_id)
                            ->increment('awarded_quantity');

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Ganador asignado exitosamente')
                            ->body("Se asignó el premio '{$prize->name}' a {$code->user->name}")
                            ->send();
                    }),
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
