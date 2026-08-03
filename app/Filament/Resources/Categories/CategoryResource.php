<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function getNavigationLabel(): string
    {
        return 'Категорії';
    }

    public static function getModelLabel(): string
    {
        return 'категорію';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Категорії';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('parent_id')->label('Батьківська категорія')->relationship('parent', 'name')->searchable()->preload(),
            TextInput::make('name')->label('Назва')->required(),
            TextInput::make('slug')->label('Адреса (slug)')->required()->unique(ignoreRecord: true),
            Textarea::make('description')->label('Опис')->columnSpanFull(),
            FileUpload::make('image_url')->label('Зображення')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(8192)->directory('categories')->visibility('public'),
            TextInput::make('position')->label('Порядок')->numeric()->default(0)->helperText('Також можна перетягувати рядки у списку.'),
            TextInput::make('seo_title')->label('SEO-заголовок'),
            Textarea::make('seo_description')->label('SEO-опис'),
            Toggle::make('is_active')->label('Активна')->default(true),
            Toggle::make('show_on_home')->label('Показувати фотокартку на головній')->helperText('Використовується фото категорії вище.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image_url')->label('Фото'),
            TextColumn::make('name')->label('Розділ / підрозділ')->searchable()->sortable()
                ->formatStateUsing(fn (string $state, Category $record): string => $record->parent_id ? '↳ '.$state : $state),
            TextColumn::make('parent.name')->label('Головний розділ')->placeholder('—'),
            TextColumn::make('position')->label('Порядок')->sortable(),
            ToggleColumn::make('show_on_home')->label('На головній'),
            IconColumn::make('is_active')->label('Активна')->boolean(),
        ])->defaultSort('position')->paginationPageOptions([5, 10, 25, 50, 100])->reorderable('position')->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCategories::route('/'), 'create' => CreateCategory::route('/create'), 'edit' => EditCategory::route('/{record}/edit')];
    }
}
