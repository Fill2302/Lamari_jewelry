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
use Filament\Forms\Components\Repeater;
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
            Select::make('mode')->label('Тип знижки')->options([
                'standard' => 'Звичайна — один відсоток',
                'quantity' => 'Залежно від кількості товарів',
            ])->default('standard')->required()->live(),
            TextInput::make('percentage')->label('Знижка, %')->numeric()->integer()->minValue(1)->maxValue(99)->suffix('%')
                ->visible(fn (Get $get): bool => ($get('mode') ?? 'standard') === 'standard')
                ->required(fn (Get $get): bool => ($get('mode') ?? 'standard') === 'standard'),
            Repeater::make('quantity_rules')->label('Правила за кількістю')
                ->schema([
                    TextInput::make('min_quantity')->label('Від кількості, шт.')->numeric()->integer()->minValue(1)->required(),
                    TextInput::make('percentage')->label('Знижка, %')->numeric()->integer()->minValue(1)->maxValue(99)->suffix('%')->required(),
                    Select::make('apply_to')->label('На які товари застосувати')->options([
                        'all' => 'На всі відповідні товари',
                        'position' => 'Лише на товар за порядком',
                    ])->default('all')->required()->live(),
                    TextInput::make('position')->label('На який товар за порядком')->numeric()->integer()->minValue(1)
                        ->helperText('Наприклад: 2 — лише на другий, 3 — лише на третій. Рахуються найдешевші відповідні товари.')
                        ->visible(fn (Get $get): bool => $get('apply_to') === 'position')
                        ->required(fn (Get $get): bool => $get('apply_to') === 'position'),
                ])
                ->columns(4)
                ->defaultItems(1)
                ->addActionLabel('Додати ще рівень')
                ->reorderable(false)
                ->helperText('Спрацьовує найвищий досягнутий рівень. Наприклад: від 1 — 5%, від 2 — 10%, від 3 — 15%; останній рівень діє і для більшої кількості.')
                ->visible(fn (Get $get): bool => $get('mode') === 'quantity')
                ->required(fn (Get $get): bool => $get('mode') === 'quantity'),
            Select::make('scope')->label('Застосувати до')->options([
                'all' => 'Усіх товарів',
                'category' => 'Розділу або підрозділу',
                'product' => 'Окремого товару',
            ])->default('all')->required()->live(),
            ViewField::make('category_ids')->label('Розділи / підрозділи')
                ->view('filament.forms.components.category-tree')
                ->viewData(fn (): array => ['categories' => static::categoryTree()])
                ->dehydrated(false)
                ->visible(fn (Get $get): bool => $get('scope') === 'category')
                ->required(fn (Get $get): bool => $get('scope') === 'category'),
            Select::make('product_ids')->label('Товари за артикулами')
                ->multiple()
                ->searchable()
                ->searchPrompt('Введіть артикул товару')
                ->noSearchResultsMessage('Товар з таким артикулом не знайдено')
                ->getSearchResultsUsing(fn (string $search): array => Product::query()
                    ->whereHas('variants', fn ($query) => $query->where('sku', 'like', '%'.$search.'%'))
                    ->with(['variants' => fn ($query) => $query->where('sku', 'like', '%'.$search.'%')])
                    ->limit(50)
                    ->get()
                    ->mapWithKeys(fn (Product $product): array => [
                        $product->id => static::productArticles($product),
                    ])
                    ->all())
                ->getOptionLabelsUsing(fn (array $values): array => Product::query()
                    ->with('variants:id,product_id,sku')
                    ->whereIn('id', $values)
                    ->get()
                    ->mapWithKeys(fn (Product $product): array => [
                        $product->id => static::productArticles($product),
                    ])
                    ->all())
                ->dehydrated(false)
                ->helperText('Можна знайти й вибрати кілька артикулів. Знижка діятиме на всі довжини кожного товару.')
                ->visible(fn (Get $get): bool => $get('scope') === 'product')
                ->required(fn (Get $get): bool => $get('scope') === 'product'),
            DateTimePicker::make('starts_at')->label('Початок дії')->helperText('Залиш порожнім, щоб увімкнути одразу.'),
            DateTimePicker::make('ends_at')->label('Кінець дії')->after('starts_at')->helperText('Залиш порожнім, якщо строк необмежений.'),
            Toggle::make('is_active')->label('Активна')->default(true),
        ]);
    }

    private static function productArticles(Product $product): string
    {
        return $product->variants
            ->pluck('sku')
            ->map(fn (string $sku): string => preg_replace('/-\d+(?:[.,]\d+)?$/u', '', $sku) ?: $sku)
            ->unique()
            ->join(', ');
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
            TextColumn::make('percentage')->label('Знижка')->formatStateUsing(
                fn (Discount $record): string => $record->mode === 'quantity' ? 'За кількістю' : $record->percentage.'%',
            )->sortable(),
            TextColumn::make('scope')->label('На що діє')->formatStateUsing(fn (Discount $record): string => match ($record->scope) {
                'all' => 'Увесь каталог',
                'category' => 'Розділів: '.$record->categories()->count(),
                'product' => 'Товарів: '.$record->products()->count(),
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
