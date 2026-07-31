<?php

namespace App\Filament\Resources\Attributes;

use App\Filament\Resources\Attributes\Pages\CreateAttribute;
use App\Filament\Resources\Attributes\Pages\EditAttribute;
use App\Filament\Resources\Attributes\Pages\ListAttributes;
use App\Models\Attribute;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    public static function getNavigationLabel(): string { return 'Фільтри й характеристики'; }
    public static function getModelLabel(): string { return 'характеристику'; }
    public static function getPluralModelLabel(): string { return 'Фільтри й характеристики'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Назва')->required(),
            TextInput::make('slug')->label('Ключ (slug)')->required()->unique(ignoreRecord: true),
            Select::make('type')->label('Тип')->options(['select' => 'Список', 'color' => 'Колір', 'text' => 'Текст'])->default('select')->required(),
            TextInput::make('position')->label('Порядок')->numeric()->default(0),
            Toggle::make('is_filterable')->label('Показувати у фільтрах')->default(true),
            Toggle::make('is_active')->label('Активна')->default(true),
            Repeater::make('values')->label('Значення')->relationship()->schema([
                TextInput::make('value')->label('Значення')->required(),
                TextInput::make('slug')->label('Ключ')->required(),
                TextInput::make('color_hex')->label('HEX-колір')->placeholder('#000000'),
                TextInput::make('position')->label('Порядок')->numeric()->default(0),
                Toggle::make('is_active')->label('Активне')->default(true),
            ])->orderColumn('position')->columns(5)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Назва')->searchable()->sortable(),
            TextColumn::make('values_count')->label('Значень')->counts('values'),
            TextColumn::make('position')->label('Порядок')->sortable(),
            IconColumn::make('is_filterable')->label('У фільтрах')->boolean(),
            IconColumn::make('is_active')->label('Активна')->boolean(),
        ])->defaultSort('position')->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAttributes::route('/'), 'create' => CreateAttribute::route('/create'), 'edit' => EditAttribute::route('/{record}/edit')];
    }
}
