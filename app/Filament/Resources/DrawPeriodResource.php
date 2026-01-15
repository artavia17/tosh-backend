<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrawPeriodResource\Pages;
use App\Filament\Resources\DrawPeriodResource\RelationManagers;
use App\Models\Code;
use App\Models\DrawPeriod;
use App\Models\PrizePool;
use App\Models\Winner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DrawPeriodResource extends Resource
{
    protected static ?string $model = DrawPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Períodos de Sorteo';

    protected static ?string $modelLabel = 'Período de Sorteo';

    protected static ?string $pluralModelLabel = 'Períodos de Sorteo';

    protected static ?string $navigationGroup = 'Gestión de Premios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Período')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: Semana 1, 01/01 - 07/01'),
                Forms\Components\Select::make('country_id')
                    ->label('País')
                    ->options(function () {
                        return \App\Models\Country::orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->native(false),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha de Inicio')
                    ->required()
                    ->native(false),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Fecha de Final')
                    ->required()
                    ->native(false)
                    ->after('start_date'),
                Forms\Components\TextInput::make('weekly_winners_target')
                    ->label('Meta de Ganadores')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Cantidad total de ganadores para este período'),

                Forms\Components\Toggle::make('is_public')
                    ->label('Mostrar ganadores públicamente')
                    ->helperText('Activar para que los ganadores de este sorteo aparezcan en la API pública')
                    ->default(false)
                    ->inline(false),

                Forms\Components\Section::make('Configuración de Premios')
                    ->description('Configure la cantidad máxima de cada premio a otorgar en este período')
                    ->schema([
                        Forms\Components\Repeater::make('prizeConfigs')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('prize_id')
                                    ->label('Premio')
                                    ->options(function () {
                                        return \App\Models\Prize::where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                Forms\Components\TextInput::make('max_quantity')
                                    ->label('Cantidad Máxima')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Máximo de este premio para este período')
                                    ->default(1),
                            ])
                            ->columns(2)
                            ->addActionLabel('Agregar Premio')
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('País')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Final')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weekly_winners_target')
                    ->label('Meta Ganadores')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('winners_count')
                    ->label('Ganadores')
                    ->counts('winners')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('draw_executed')
                    ->label('Sorteo Ejecutado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Público')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('draw_executed_at')
                    ->label('Fecha Ejecución')
                    ->dateTime()
                    ->toggleable(),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->label('País')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('draw_executed')
                    ->label('Estado Sorteo')
                    ->placeholder('Todos')
                    ->trueLabel('Ejecutados')
                    ->falseLabel('Pendientes'),
            ])
            ->actions([
                Tables\Actions\Action::make('executeDraw')
                    ->label('Ejecutar Sorteo')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Ejecutar Sorteo Semanal')
                    ->modalDescription(fn ($record) => "¿Desea ejecutar el sorteo para {$record->name}? Se seleccionarán {$record->weekly_winners_target} ganadores de forma aleatoria.")
                    ->modalSubmitActionLabel('Ejecutar Sorteo')
                    ->visible(fn ($record) => !$record->draw_executed)
                    ->action(function ($record) {
                        return static::executeWeeklyDraw($record);
                    })
                    ->successNotificationTitle('Sorteo ejecutado exitosamente')
                    ->after(function () {
                        redirect()->route('filament.admin.resources.draw-periods.index');
                    }),
                Tables\Actions\EditAction::make()->label('Editar')
                    ->visible(fn ($record) => !$record->draw_executed),
                Tables\Actions\DeleteAction::make()->label('Eliminar')
                    ->visible(fn ($record) => !$record->draw_executed),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
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
            'index' => Pages\ListDrawPeriods::route('/'),
            'create' => Pages\CreateDrawPeriod::route('/create'),
            'edit' => Pages\EditDrawPeriod::route('/{record}/edit'),
        ];
    }

    protected static function executeWeeklyDraw(DrawPeriod $period)
    {
        // Obtener facturas registradas en este período que no hayan ganado
        $eligibleCodes = Code::whereHas('user', function ($query) use ($period) {
                $query->where('country_id', $period->country_id);
            })
            ->whereBetween('created_at', [$period->start_date, $period->end_date->addDay()])
            ->whereDoesntHave('winner')
            ->inRandomOrder()
            ->get();

        if ($eligibleCodes->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('No hay facturas elegibles')
                ->body('No se encontraron facturas registradas en este período que no hayan ganado.')
                ->send();
            return;
        }

        // Obtener la configuración de premios para este período
        $periodPrizes = $period->prizes()->get();

        if ($periodPrizes->isEmpty()) {
            Notification::make()
                ->danger()
                ->title('No hay premios configurados')
                ->body('Configure los premios y cantidades máximas para este período antes de ejecutar el sorteo.')
                ->send();
            return;
        }

        // Obtener el pool de premios general para validar inventario
        $prizePools = PrizePool::where('country_id', $period->country_id)->get()->keyBy('prize_id');

        // Calcular cuántos ganadores podemos asignar
        $winnersToAssign = min($period->weekly_winners_target, $eligibleCodes->count());

        // Distribuir premios aleatoriamente
        $assignedWinners = 0;

        // Obtener TODOS los premios que cada usuario ya ganó en TODA LA CAMPAÑA (todos los períodos)
        // Estructura: [user_id => [prize_id_1, prize_id_2, ...]]
        $userPrizeWinners = Winner::where('country_id', $period->country_id)
            ->get()
            ->groupBy('user_id')
            ->map(function ($winners) {
                return $winners->pluck('prize_id')->unique()->toArray();
            })
            ->toArray();

        // Rastrear qué usuarios ya ganaron en ESTE PERÍODO (para limitar a 1 premio por sorteo)
        $usersWonInThisPeriod = [];

        foreach ($eligibleCodes as $code) {
            if ($assignedWinners >= $winnersToAssign) {
                break; // Ya alcanzamos el máximo de ganadores
            }

            // NUEVO: Verificar si este usuario ya ganó en este período
            if (in_array($code->user_id, $usersWonInThisPeriod)) {
                continue; // Este usuario ya ganó 1 premio en este sorteo, pasar al siguiente código
            }

            // Filtrar premios disponibles que el usuario aún no ha ganado EN TODA LA CAMPAÑA
            $availablePrizes = $periodPrizes->filter(function ($periodPrize) use ($prizePools, $code, $userPrizeWinners) {
                $prizePool = $prizePools->get($periodPrize->id);

                // Verificar que:
                // 1. No hayamos alcanzado el máximo para este período
                // 2. Haya stock en el inventario general
                // 3. Este usuario NO haya ganado este premio NUNCA (en ningún período anterior)
                $userAlreadyWonThisPrize = isset($userPrizeWinners[$code->user_id])
                    && in_array($periodPrize->id, $userPrizeWinners[$code->user_id]);

                return $periodPrize->pivot->awarded_quantity < $periodPrize->pivot->max_quantity
                    && $prizePool
                    && $prizePool->remaining > 0
                    && !$userAlreadyWonThisPrize;
            });

            if ($availablePrizes->isEmpty()) {
                continue; // Este usuario ya no puede ganar ningún premio, pasar al siguiente código
            }

            $selectedPrize = $availablePrizes->random();
            $prizePool = $prizePools->get($selectedPrize->id);

            // Crear ganador
            Winner::create([
                'user_id' => $code->user_id,
                'code_id' => $code->id,
                'prize_id' => $selectedPrize->id,
                'draw_period_id' => $period->id,
                'country_id' => $period->country_id,
            ]);

            // NUEVO: Registrar que este usuario ya ganó en ESTE período
            $usersWonInThisPeriod[] = $code->user_id;

            // Registrar que este usuario ganó este premio (para toda la campaña)
            if (!isset($userPrizeWinners[$code->user_id])) {
                $userPrizeWinners[$code->user_id] = [];
            }
            $userPrizeWinners[$code->user_id][] = $selectedPrize->id;

            // Actualizar cantidad de premios otorgados en el inventario general
            $prizePool->increment('awarded_quantity');
            $prizePool->refresh();
            $prizePools->put($selectedPrize->id, $prizePool);

            // Actualizar cantidad de premios otorgados en este período
            $period->prizes()->updateExistingPivot($selectedPrize->id, [
                'awarded_quantity' => \DB::raw('awarded_quantity + 1')
            ]);

            // Refrescar el período para obtener los valores actualizados
            $periodPrizes = $period->prizes()->get();

            $assignedWinners++;
        }

        // Marcar el sorteo como ejecutado
        $period->update([
            'draw_executed' => true,
            'draw_executed_at' => now(),
        ]);

        // Si no se asignaron todos los ganadores, acumular para siguiente semana
        $pendingWinners = $period->weekly_winners_target - $assignedWinners;

        Notification::make()
            ->success()
            ->title('Sorteo ejecutado exitosamente')
            ->body("Se asignaron {$assignedWinners} ganadores." .
                   ($pendingWinners > 0 ? " {$pendingWinners} premios acumulados para próxima semana." : ''))
            ->send();
    }
}
