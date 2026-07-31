<?php

namespace App\Filament\Resources\IntegrationCredentials;

use App\Filament\Resources\IntegrationCredentials\Pages\EditIntegrationCredential;
use App\Filament\Resources\IntegrationCredentials\Pages\ListIntegrationCredentials;
use App\Models\IntegrationCredential;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IntegrationCredentialResource extends Resource
{
    protected static ?string $model = IntegrationCredential::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    public static function getNavigationLabel(): string { return 'Інтеграції'; }
    public static function getModelLabel(): string { return 'інтеграцію'; }
    public static function getPluralModelLabel(): string { return 'Інтеграції'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('provider'),
            Section::make('Нова пошта')
                ->description('API-ключ зберігається зашифрованим і після збереження більше не показується.')
                ->schema([
                    TextInput::make('api_key')
                        ->label('API-ключ')
                        ->password()
                        ->revealable()
                        ->placeholder(fn (IntegrationCredential $record): string => filled($record->api_key)
                            ? 'Ключ уже налаштовано. Залиште поле порожнім, щоб не змінювати.'
                            : 'Вставте API-ключ Нової пошти')
                        ->afterStateHydrated(fn (TextInput $component) => $component->state(''))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Toggle::make('is_active')->label('Інтеграція активна'),
                ])->columns(2),
            Section::make('Дані відправника')->schema([
                TextInput::make('sender_name')->label('Відправник')->required(),
                TextInput::make('sender_phone')->label('Телефон')->tel()->required(),
                TextInput::make('sender_settlement')->label('Населений пункт')->required(),
                TextInput::make('sender_warehouse')->label('Номер відділення')->required(),
                TextInput::make('package_weight')->label('Вага, кг')->numeric()->required(),
                TextInput::make('package_length')->label('Довжина, см')->numeric()->required(),
                TextInput::make('package_width')->label('Ширина, см')->numeric()->required(),
                TextInput::make('package_height')->label('Висота, см')->numeric()->required(),
                Select::make('delivery_payer')->label('Хто оплачує доставку')->options([
                    'Recipient' => 'Покупець',
                    'Sender' => 'Lamari',
                ])->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')->label('Сервіс')->formatStateUsing(fn (): string => 'Нова пошта'),
                IconColumn::make('api_key')->label('Ключ налаштовано')->boolean()->getStateUsing(fn (IntegrationCredential $record): bool => filled($record->api_key)),
                IconColumn::make('is_active')->label('Активна')->boolean(),
                TextColumn::make('updated_at')->label('Оновлено')->dateTime('d.m.Y H:i'),
            ])
            ->recordActions([EditAction::make()->label('Налаштувати')]);
    }

    public static function canCreate(): bool { return false; }
    public static function canDelete(mixed $record): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListIntegrationCredentials::route('/'),
            'edit' => EditIntegrationCredential::route('/{record}/edit'),
        ];
    }
}
