<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
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
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Категорія / підкатегорія')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
