<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('material'),
                Textarea::make('packaging_text')->columnSpanFull(),
                Textarea::make('care_text')->columnSpanFull(),
                Repeater::make('media')->relationship()->schema([
                    Select::make('type')->options(['image' => 'Фото', 'video' => 'Відео'])->required(),
                    FileUpload::make('url')->label('Фото або відео')->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm'])->directory('products')->visibility('public')->required(),
                    FileUpload::make('poster_url')->label('Poster відео')->image()->directory('products/posters')->visibility('public'),
                    TextInput::make('alt')->label('Alt / опис'),
                    TextInput::make('position')->numeric()->default(0),
                    Toggle::make('is_active')->default(true),
                ])->orderColumn('position')->columns(3)->columnSpanFull(),
                TextInput::make('seo_title'),
                TextInput::make('seo_description'),
                Repeater::make('variants')->relationship()->schema([
                    TextInput::make('sku')->required(),
                    TextInput::make('name')->required(),
                    TextInput::make('price_amount')->label('Price (kopiyky)')->numeric()->required(),
                    TextInput::make('stock_on_hand')->numeric()->required(),
                    Toggle::make('is_active')->default(true),
                ])->columns(5)->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('published_at'),
            ]);
    }
}
