<?php

namespace App\Filament\Resources\HomepageSettings;

use App\Filament\Resources\HomepageSettings\Pages\EditHomepageSetting;
use App\Filament\Resources\HomepageSettings\Pages\ListHomepageSettings;
use App\Models\HomepageSetting;
use App\Services\MediaOptimizer;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomepageSettingResource extends Resource
{
    protected static ?string $model = HomepageSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    public static function getNavigationLabel(): string
    {
        return 'Головна сторінка';
    }

    public static function getModelLabel(): string
    {
        return 'головну сторінку';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Головна сторінка';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Головний банер')->description('Окремі файли для комп’ютера і телефона')->schema([
                FileUpload::make('desktop_hero_image')->label('Фото для комп’ютера')->image()->acceptedFileTypes(MediaOptimizer::IMAGE_ACCEPTED_MIME_TYPES)->maxSize(8192)->directory('homepage')->visibility('public'),
                FileUpload::make('mobile_hero_video')->label('Відео для телефона')->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])->directory('homepage')->visibility('public')->maxSize(51200),
                FileUpload::make('mobile_hero_poster')->label('Заставка відео')->image()->acceptedFileTypes(MediaOptimizer::IMAGE_ACCEPTED_MIME_TYPES)->maxSize(8192)->directory('homepage')->visibility('public'),
                TextInput::make('hero_link')->label('Куди веде натискання')->default('/catalog')->required(),
            ])->columns(2)->collapsible(),
            Section::make('Стрічка та товарні блоки')->schema([
                TextInput::make('ticker_text')->label('Текст бігучої стрічки')->required()->columnSpanFull(),
                Toggle::make('show_new_products')->label('Показувати «Новинки»'),
                TextInput::make('new_products_title')->label('Заголовок новинок')->required(),
                Toggle::make('show_hit_products')->label('Показувати «Хіти продажів»'),
                TextInput::make('hit_products_title')->label('Заголовок хітів')->required(),
            ])->columns(2)->collapsible(),
            Section::make('Поширені питання')->schema([
                Repeater::make('faq_items')->label('Питання та відповіді')->schema([
                    TextInput::make('question')->label('Питання')->required(),
                    Textarea::make('answer')->label('Відповідь')->required()->rows(4),
                ])->columns(1)->reorderable()->collapsible()->itemLabel(fn (array $state): ?string => $state['question'] ?? null)->columnSpanFull(),
            ])->collapsible(),
            Section::make('Instagram-відгуки')->schema([
                TextInput::make('instagram_title')->label('Заголовок')->required(),
                TextInput::make('instagram_url')->label('Посилання Instagram')->url(),
                Textarea::make('instagram_text')->label('Текст')->columnSpanFull(),
                FileUpload::make('instagram_images')->label('Фотографії')->multiple()->reorderable()->image()->acceptedFileTypes(MediaOptimizer::IMAGE_ACCEPTED_MIME_TYPES)->maxSize(8192)->directory('homepage/instagram')->visibility('public')->columnSpanFull(),
            ])->columns(2)->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('updated_at')->label('Останнє оновлення')->dateTime('d.m.Y H:i'),
        ])->recordActions([EditAction::make()]);
    }

    public static function canCreate(): bool
    {
        return HomepageSetting::query()->doesntExist();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomepageSettings::route('/'),
            'edit' => EditHomepageSetting::route('/{record}/edit'),
        ];
    }
}
