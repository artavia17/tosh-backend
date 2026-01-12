<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrizePoolResource\Pages;
use App\Filament\Resources\PrizePoolResource\RelationManagers;
use App\Models\PrizePool;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrizePoolResource extends Resource
{
    protected static ?string $model = PrizePool::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Inventario de Premios';

    protected static ?string $modelLabel = 'Inventario de Premio';

    protected static ?string $pluralModelLabel = 'Inventario de Premios';

    protected static ?string $navigationGroup = 'Gestión de Premios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('country_id')
                    ->label('País')
                    ->options(function () {
                        return \App\Models\Country::orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->native(false),
                Forms\Components\Select::make('prize_id')
                    ->label('Premio')
                    ->options(function () {
                        return \App\Models\Prize::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->native(false),
                Forms\Components\TextInput::make('total_quantity')
                    ->label('Cantidad Total')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Total de premios disponibles para este país'),
                Forms\Components\TextInput::make('awarded_quantity')
                    ->label('Premios Otorgados')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Se actualiza automáticamente al ejecutar sorteos'),
                Forms\Components\TextInput::make('weekly_target')
                    ->label('Meta Semanal')
                    ->numeric()
                    ->default(0)
                    ->helperText('Objetivo de premios por semana (opcional)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->label('País')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prize.name')
                    ->label('Premio')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('awarded_quantity')
                    ->label('Otorgados')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Restantes')
                    ->state(fn ($record) => $record->remaining)
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state > 100 => 'success',
                        $state > 50 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('weekly_target')
                    ->label('Meta Semanal')
                    ->numeric()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Eliminar'),
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
            'index' => Pages\ListPrizePools::route('/'),
            'create' => Pages\CreatePrizePool::route('/create'),
            'edit' => Pages\EditPrizePool::route('/{record}/edit'),
        ];
    }
}
