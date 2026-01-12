<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->maxLength(255),
                Forms\Components\Select::make('country_id')
                    ->relationship('country', 'name'),
                Forms\Components\TextInput::make('id_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('id_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Toggle::make('marketing_opt_in')
                    ->required(),
                Forms\Components\Toggle::make('whatsapp_opt_in')
                    ->required(),
                Forms\Components\Toggle::make('phone_opt_in')
                    ->required(),
                Forms\Components\Toggle::make('email_opt_in')
                    ->required(),
                Forms\Components\Toggle::make('sms_opt_in')
                    ->required(),
                Forms\Components\Toggle::make('data_treatment_accepted')
                    ->required(),
                Forms\Components\Toggle::make('terms_accepted')
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('country.name'),
                        Infolists\Components\TextEntry::make('phone_number'),
                        Infolists\Components\TextEntry::make('id_type'),
                        Infolists\Components\TextEntry::make('id_number'),
                    ])->columns(2),
                Infolists\Components\Section::make('Consents')
                    ->schema([
                        Infolists\Components\IconEntry::make('marketing_opt_in')->boolean(),
                        Infolists\Components\IconEntry::make('whatsapp_opt_in')->boolean(),
                        Infolists\Components\IconEntry::make('phone_opt_in')->boolean(),
                        Infolists\Components\IconEntry::make('email_opt_in')->boolean(),
                        Infolists\Components\IconEntry::make('sms_opt_in')->boolean(),
                        Infolists\Components\IconEntry::make('data_treatment_accepted')->boolean(),
                        Infolists\Components\IconEntry::make('terms_accepted')->boolean(),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->searchable()
                    ->label('Identification'),
                Tables\Columns\TextColumn::make('country.name')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('country', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $users = $livewire->getFilteredTableQuery()->with('country')->get();
                        $csv = \League\Csv\Writer::createFromString('');
                        $csv->insertOne([
                            'Name',
                            'Email',
                            'ID Type',
                            'ID Number',
                            'Country',
                            'Phone',
                            'Marketing Opt-in',
                            'WhatsApp Opt-in',
                            'Phone Opt-in',
                            'Email Opt-in',
                            'SMS Opt-in',
                            'Data Treatment',
                            'Terms Accepted',
                            'Created At',
                            'Updated At'
                        ]);

                        foreach ($users as $user) {
                            $csv->insertOne([
                                $user->name,
                                $user->email,
                                $user->id_type,
                                $user->id_number,
                                $user->country?->name,
                                $user->phone_number,
                                $user->marketing_opt_in ? 'Yes' : 'No',
                                $user->whatsapp_opt_in ? 'Yes' : 'No',
                                $user->phone_opt_in ? 'Yes' : 'No',
                                $user->email_opt_in ? 'Yes' : 'No',
                                $user->sms_opt_in ? 'Yes' : 'No',
                                $user->data_treatment_accepted ? 'Yes' : 'No',
                                $user->terms_accepted ? 'Yes' : 'No',
                                $user->created_at,
                                $user->updated_at,
                            ]);
                        }

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv->toString();
                        }, 'users-export-' . date('Y-m-d-His') . '.csv');
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
