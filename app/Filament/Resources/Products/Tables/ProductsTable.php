<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Category;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Категорія')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Товар')
                    ->searchable(),
                TextColumn::make('article')
                    ->label('Артикул')
                    ->getStateUsing(fn ($record): string => $record->variants
                        ->pluck('sku')
                        ->map(fn (string $sku): string => preg_replace('/-\d+$/', '', $sku))
                        ->unique()
                        ->implode(', '))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('variants', fn (Builder $variants): Builder => $variants
                            ->where('sku', 'like', "%{$search}%"))),
                SelectColumn::make('payment_destination')
                    ->label('Банк оплати')
                    ->options([
                        'unassigned' => 'Не визначено',
                        'mono' => 'ФОП-2',
                        'privat' => 'ФОП-3',
                    ]),
                TextInputColumn::make('catalog_position')
                    ->label('Місце в каталозі')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:1'])
                    ->sortable(),
                TextInputColumn::make('category_position')
                    ->label('Місце в категорії')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:1'])
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('material')
                    ->searchable(),
                ImageColumn::make('image_url'),
                TextColumn::make('seo_title')
                    ->searchable(),
                TextColumn::make('seo_description')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('catalog_position')
            ->paginationPageOptions([5, 10, 25, 50, 100])
            ->filters([
                SelectFilter::make('parent_category_id')
                    ->label('Категорія')
                    ->options(fn (): array => Category::query()
                        ->whereNull('parent_id')
                        ->orderBy('position')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $query, int|string $categoryId): Builder => $query
                            ->whereHas('category', fn (Builder $category): Builder => $category
                                ->whereKey($categoryId)
                                ->orWhere('parent_id', $categoryId))))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('subcategory_id')
                    ->label('Підкатегорія')
                    ->options(fn (): array => Category::query()
                        ->whereNotNull('parent_id')
                        ->with('parent:id,name')
                        ->orderBy('position')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Category $category): array => [
                            $category->id => "{$category->parent?->name} — {$category->name}",
                        ])
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $query, int|string $categoryId): Builder => $query
                            ->where('category_id', $categoryId)))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_destination')
                    ->label('Банк оплати')
                    ->options([
                        'unassigned' => 'Не визначено',
                        'mono' => 'ФОП-2',
                        'privat' => 'ФОП-3',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('setPaymentDestination')
                        ->label('Змінити банк оплати')
                        ->schema([
                            Select::make('payment_destination')
                                ->label('Банк для онлайн-оплати')
                                ->options([
                                    'unassigned' => 'Ще не визначено',
                                    'mono' => 'ФОП-2',
                                    'privat' => 'ФОП-3',
                                ])
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update([
                            'payment_destination' => $data['payment_destination'],
                        ]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
