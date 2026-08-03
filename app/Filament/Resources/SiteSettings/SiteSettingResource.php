<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\CreateSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function getNavigationLabel(): string
    {
        return 'Налаштування сайту';
    }

    public static function getModelLabel(): string
    {
        return 'налаштування';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Налаштування сайту';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('group')->label('Розділ')->options(['general' => 'Загальні', 'contacts' => 'Контакти й соцмережі', 'homepage' => 'Головна сторінка', 'delivery' => 'Доставка', 'payment' => 'Оплата'])->required(),
            TextInput::make('label')->label('Назва')->required(),
            TextInput::make('key')->label('Системний ключ')->required()->unique(ignoreRecord: true),
            Select::make('type')->label('Тип')->options(['text' => 'Короткий текст', 'textarea' => 'Довгий текст', 'image' => 'Зображення', 'boolean' => 'Так/ні'])->default('text')->required()->live(),
            Textarea::make('value')->label('Значення')->visible(fn ($get) => $get('type') !== 'image')->columnSpanFull(),
            FileUpload::make('value')->label('Зображення')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(8192)->directory('settings')->visibility('public')->visible(fn ($get) => $get('type') === 'image')->columnSpanFull(),
            Toggle::make('is_public')->label('Доступне на сайті')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('group')->label('Розділ')->badge(),
            TextColumn::make('label')->label('Назва')->searchable(),
            TextColumn::make('value')->label('Значення')->limit(60),
            TextColumn::make('updated_at')->label('Оновлено')->dateTime('d.m.Y H:i'),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSiteSettings::route('/'), 'create' => CreateSiteSetting::route('/create'), 'edit' => EditSiteSetting::route('/{record}/edit')];
    }
}
