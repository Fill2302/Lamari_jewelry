<?php

namespace App\Filament\Resources\ProductCardSettings;

use App\Filament\Resources\ProductCardSettings\Pages\EditProductCardSetting;
use App\Filament\Resources\ProductCardSettings\Pages\ListProductCardSettings;
use App\Models\ProductCardSetting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductCardSettingResource extends Resource
{
    protected static ?string $model = ProductCardSetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function getNavigationLabel(): string { return 'Тексти картки товару'; }
    public static function getModelLabel(): string { return 'тексти картки товару'; }
    public static function getPluralModelLabel(): string { return 'Тексти картки товару'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Назви розділів')->description('Ці назви однакові для всіх товарів')->schema([
                TextInput::make('characteristics_title')->label('Характеристики')->required(),
                TextInput::make('description_title')->label('Опис товару')->required(),
                TextInput::make('packaging_title')->label('Упаковка')->required(),
                TextInput::make('care_title')->label('Догляд')->required(),
                TextInput::make('delivery_payment_title')->label('Доставка та оплата')->required(),
            ])->columns(2),
            Section::make('Доставка та оплата')->schema([
                Textarea::make('delivery_text')->label('Текст про доставку')->rows(4)->required(),
                Textarea::make('payment_text')->label('Текст про оплату')->rows(4)->required(),
            ]),
            Section::make('Запитання та відповіді')->schema([
                TextInput::make('warranty_question')->label('Запитання про гарантію')->required(),
                Textarea::make('warranty_answer')->label('Відповідь про гарантію')->rows(4)->required(),
                TextInput::make('returns_question')->label('Запитання про обмін і повернення')->required(),
                Textarea::make('returns_answer')->label('Відповідь про обмін і повернення')->rows(4)->required(),
                TextInput::make('water_question')->label('Запитання про контакт із водою')->required(),
                Textarea::make('water_answer')->label('Відповідь про контакт із водою')->rows(4)->required(),
                TextInput::make('tarnish_question')->label('Запитання про потемніння')->required(),
                Textarea::make('tarnish_answer')->label('Відповідь про потемніння')->rows(4)->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('updated_at')->label('Останнє оновлення')->dateTime('d.m.Y H:i'),
        ]);
    }

    public static function canCreate(): bool { return ProductCardSetting::query()->doesntExist(); }
    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListProductCardSettings::route('/'),
            'edit' => EditProductCardSetting::route('/{record}/edit'),
        ];
    }
}
