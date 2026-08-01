<?php

namespace App\Filament\Resources\Discounts;

use App\Filament\Resources\Discounts\Pages\CreateDiscount;
use App\Filament\Resources\Discounts\Pages\EditDiscount;
use App\Filament\Resources\Discounts\Pages\ListDiscounts;
use App\Models\Discount;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    public static function getNavigationLabel(): string { return 'Знижки'; }
    public static function getModelLabel(): string { return 'знижку'; }
    public static function getPluralModelLabel(): string { return 'Знижки'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Назва знижки')->required()->maxLength(120),
            TextInput::make('percentage')->label('Знижка, %')->numeric()->integer()->minValue(1)->maxValue(99)->suffix('%')->required(),
            Select::make('scope')->label('Застосувати до')->options([
                'all' => 'Усіх товарів',
                'category' => 'Розділу або підрозділу',
                'product' => 'Окремого товару',
            ])->default('all')->required()->live(),
            Select::make('category_id')->label('Розділ / підрозділ')
                ->relationship('category', 'name')->searchable()->preload()
                ->visible(fn (Get $get): bool => $get('scope') === 'category')
                ->required(fn (Get $get): bool => $get('scope') === 'category'),
            Select::make('product_id')->label('Товар')
                ->relationship('product', 'name')->searchable()->preload()
                ->visible(fn (Get $get): bool => $get('scope') === 'product')
                ->required(fn (Get $get): bool => $get('scope') === 'product'),
            DateTimePicker::make('starts_at')->label('Початок дії')->helperText('Залиш порожнім, щоб увімкнути одразу.'),
            DateTimePicker::make('ends_at')->label('Кінець дії')->after('starts_at')->helperText('Залиш порожнім, якщо строк необмежений.'),
            Toggle::make('is_active')->label('Активна')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Назва')->searchable(),
            TextColumn::make('percentage')->label('Знижка')->suffix('%')->sortable(),
            TextColumn::make('scope')->label('На що діє')->formatStateUsing(fn (Discount $record): string => match ($record->scope) {
                'all' => 'Увесь каталог',
                'category' => 'Розділ: '.($record->category?->name ?? 'видалено'),
                'product' => 'Товар: '.($record->product?->name ?? 'видалено'),
                default => $record->scope,
            }),
            TextColumn::make('starts_at')->label('Початок')->dateTime('d.m.Y H:i')->placeholder('Одразу'),
            TextColumn::make('ends_at')->label('Завершення')->dateTime('d.m.Y H:i')->placeholder('Безстроково'),
            IconColumn::make('is_active')->label('Активна')->boolean(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListDiscounts::route('/'), 'create' => CreateDiscount::route('/create'), 'edit' => EditDiscount::route('/{record}/edit')];
    }
}
