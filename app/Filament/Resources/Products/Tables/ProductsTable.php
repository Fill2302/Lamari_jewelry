<?php

namespace App\Filament\Resources\Products\Tables;

use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use Filament\Tables\Table;

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
                        'mono' => 'monobank',
                        'privat' => 'ПриватБанк',
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
                SelectFilter::make('category_id')
                    ->label('Категорія / підкатегорія')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_destination')
                    ->label('Банк оплати')
                    ->options([
                        'unassigned' => 'Не визначено',
                        'mono' => 'monobank',
                        'privat' => 'ПриватБанк',
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
                                    'mono' => 'monobank',
                                    'privat' => 'ПриватБанк',
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
