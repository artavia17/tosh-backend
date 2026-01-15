<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CodeResource\Pages;
use App\Filament\Resources\CodeResource\RelationManagers;
use App\Models\Code;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CodeResource extends Resource
{
    protected static ?string $model = Code::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Facturas';

    protected static ?string $modelLabel = 'Factura';

    protected static ?string $pluralModelLabel = 'Facturas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('invoice_path')
                    ->label('Ruta de Factura')
                    ->disabled()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Factura')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.country.name')
                    ->label('País')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('invoice_path')
                    ->label('Factura')
                    ->disk('public')
                    ->square()
                    ->size(60),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Subida')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('user.country', 'name')
                    ->label('País'),
            ])
            ->actions([
                // No actions - view only
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $codes = $livewire->getFilteredTableQuery()->with(['user', 'user.country'])->get();
                        $csv = \League\Csv\Writer::createFromString('');
                        $csv->insertOne([
                            'ID Factura',
                            'Nombre Usuario',
                            'País',
                            'Ruta Factura',
                            'Fecha Subida',
                        ]);

                        foreach ($codes as $code) {
                            $csv->insertOne([
                                $code->id,
                                $code->user?->name,
                                $code->user?->country?->name,
                                $code->invoice_path,
                                $code->created_at,
                            ]);
                        }

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv->toString();
                        }, 'facturas-export-' . date('Y-m-d-His') . '.csv');
                    }),
            ])
            ->bulkActions([
                // No bulk actions
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
            'index' => Pages\ListCodes::route('/'),
            // 'create' => Pages\CreateCode::route('/create'),
            // 'edit' => Pages\EditCode::route('/{record}/edit'),
        ];
    }
}
