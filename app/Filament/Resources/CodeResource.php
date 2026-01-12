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

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Códigos';

    protected static ?string $modelLabel = 'Código';

    protected static ?string $pluralModelLabel = 'Códigos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.country.name')
                    ->label('Country')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('user.country', 'name')
                    ->label('Country'),
            ])
            ->actions([
                // No actions - view only
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $codes = $livewire->getFilteredTableQuery()->with(['user', 'user.country'])->get();
                        $csv = \League\Csv\Writer::createFromString('');
                        $csv->insertOne([
                            'User Name',
                            'Country',
                            'Code',
                            'Created At',
                        ]);

                        foreach ($codes as $code) {
                            $csv->insertOne([
                                $code->user?->name,
                                $code->user?->country?->name,
                                $code->code,
                                $code->created_at,
                            ]);
                        }

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv->toString();
                        }, 'codes-export-' . date('Y-m-d-His') . '.csv');
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
