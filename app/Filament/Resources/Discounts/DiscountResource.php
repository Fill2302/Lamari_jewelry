<?php

namespace App\Filament\Resources\Discounts;

use App\Filament\Resources\Discounts\Pages\CreateDiscount;
use App\Filament\Resources\Discounts\Pages\EditDiscount;
use App\Filament\Resources\Discounts\Pages\ListDiscounts;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
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

    public static function getNavigationLabel(): string
    {
        return 'Знижки';
    }

    public static function getModelLabel(): string
    {
        return 'знижку';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Знижки';
    }

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
            ViewField::make('category_id')->label('Розділ / підрозділ')
                ->view('filament.forms.components.category-tree')
                ->viewData(fn (): array => ['categories' => static::categoryTree()])
                ->visible(fn (Get $get): bool => $get('scope') === 'category')
                ->required(fn (Get $get): bool => $get('scope') === 'category'),
            Select::make('product_id')->label('Товар за артикулом')
                ->searchable()
                ->searchPrompt('Введіть артикул товару')
                ->noSearchResultsMessage('Товар з таким артикулом не знайдено')
                ->getSearchResultsUsing(fn (string $search): array => Product::query()
                    ->whereHas('variants', fn ($query) => $query->where('sku', 'like', '%'.$search.'%'))
                    ->with(['variants' => fn ($query) => $query->where('sku', 'like', '%'.$search.'%')])
                    ->limit(50)
                    ->get()
                    ->mapWithKeys(fn (Product $product): array => [
                        $product->id => $product->variants->pluck('sku')->join(', '),
                    ])
                    ->all())
                ->getOptionLabelUsing(fn ($value): ?string => Product::query()
                    ->with('variants:id,product_id,sku')
                    ->find($value)?->variants->pluck('sku')->join(', '))
                ->helperText('Пошук виконується за артикулом, а не за назвою товару.')
                ->visible(fn (Get $get): bool => $get('scope') === 'product')
                ->required(fn (Get $get): bool => $get('scope') === 'product'),
            DateTimePicker::make('starts_at')->label('Початок дії')->helperText('Залиш порожнім, щоб увімкнути одразу.'),
            DateTimePicker::make('ends_at')->label('Кінець дії')->after('starts_at')->helperText('Залиш порожнім, якщо строк необмежений.'),
            Toggle::make('is_active')->label('Активна')->default(true),
        ]);
    }

    private static function categoryTree(): array
    {
        $categories = Category::query()
            ->orderBy('position')
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);

        $build = function (?int $parentId) use (&$build, $categories): array {
            return $categories
                ->where('parent_id', $parentId)
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'children' => $build($category->id),
                ])
                ->values()
                ->all();
        };

        return $build(null);
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
