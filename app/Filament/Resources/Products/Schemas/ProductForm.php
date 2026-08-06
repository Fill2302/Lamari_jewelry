<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Services\MediaOptimizer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Категорія / підкатегорія')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('payment_destination')
                    ->label('Банк для онлайн-оплати')
                    ->options([
                        'unassigned' => 'Ще не визначено',
                        'mono' => 'monobank',
                        'privat' => 'ПриватБанк',
                    ])
                    ->default('unassigned')
                    ->helperText('Визначає, на який платіжний акаунт буде направлене онлайн-замовлення з цим товаром.')
                    ->required(),
                TextInput::make('catalog_position')
                    ->label('Місце в загальному каталозі')
                    ->helperText('1 — перший товар, 2 — другий і так далі.')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(1000)
                    ->required(),
                TextInput::make('category_position')
                    ->label('Місце в категорії / підкатегорії')
                    ->helperText('Порядок товару у вибраній вище категорії та її батьківському розділі.')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->default(1000)
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('material'),
                Select::make('attributeValues')
                    ->label('Характеристики й фільтри')
                    ->relationship('attributeValues', 'value')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Textarea::make('packaging_text')->columnSpanFull(),
                Textarea::make('care_text')->columnSpanFull(),
                Toggle::make('size_guide_enabled')
                    ->label('Показувати підказку «Як визначити розмір»')
                    ->helperText('Увімкніть для товарів, у яких покупець обирає довжину або розмір.')
                    ->live()
                    ->default(false)
                    ->columnSpanFull(),
                Select::make('size_guide_type')
                    ->label('Тип підказки розміру')
                    ->options([
                        'necklace' => 'Кольє, чокер або ланцюжок',
                        'bracelet' => 'Браслет або анклет',
                        'ring' => 'Каблучка',
                    ])
                    ->required(fn (Get $get): bool => (bool) $get('size_guide_enabled'))
                    ->visible(fn (Get $get): bool => (bool) $get('size_guide_enabled')),
                TextInput::make('size_guide_label')
                    ->label('Напис над варіантами розміру')
                    ->placeholder('Залиште порожнім, щоб показувати лише посилання')
                    ->helperText('Можна ввести власний напис або вибрати один із запропонованих.')
                    ->datalist([
                        'Виберіть розмір + 6 см подовжувач:',
                        'Виберіть розмір впритул:',
                    ])
                    ->visible(fn (Get $get): bool => (bool) $get('size_guide_enabled')),
                Textarea::make('delivery_payment_text')
                    ->label('Доставка та оплата')
                    ->columnSpanFull(),
                Repeater::make('media')
                    ->label('Галерея товару')
                    ->helperText('Перетягуйте елементи за маркер або використовуйте стрілки. Перший елемент буде обкладинкою товару.')
                    ->relationship()
                    ->schema([
                        Placeholder::make('preview')
                            ->label('Попередній перегляд')
                            ->content(function (Get $get): HtmlString {
                                $path = $get('url');

                                if (is_array($path)) {
                                    $path = reset($path);
                                }

                                if (! is_string($path) || blank($path)) {
                                    return new HtmlString('<div style="padding:2rem;text-align:center;background:#f5f1ed;border-radius:12px;color:#7a6a60">Завантажте фото або відео</div>');
                                }

                                $url = e(asset('storage/'.ltrim($path, '/')));

                                if ($get('type') === 'video') {
                                    return new HtmlString("<video src=\"{$url}\" controls muted playsinline preload=\"metadata\" style=\"display:block;width:100%;max-height:420px;object-fit:contain;background:#eee7e1;border-radius:12px\"></video>");
                                }

                                return new HtmlString("<img src=\"{$url}\" alt=\"Попередній перегляд\" style=\"display:block;width:100%;max-height:420px;object-fit:contain;background:#eee7e1;border-radius:12px\">");
                            })
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Тип')
                            ->options(['image' => 'Фото', 'video' => 'Відео'])
                            ->live()
                            ->required(),
                        FileUpload::make('url')
                            ->label('Фото або відео')
                            ->acceptedFileTypes([...MediaOptimizer::IMAGE_ACCEPTED_MIME_TYPES, 'video/mp4', 'video/webm', 'video/quicktime'])
                            ->maxSize(51200)
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->openable()
                            ->downloadable()
                            ->live()
                            ->required(),
                        FileUpload::make('poster_url')
                            ->label('Обкладинка відео')
                            ->image()
                            ->acceptedFileTypes(MediaOptimizer::IMAGE_ACCEPTED_MIME_TYPES)
                            ->maxSize(8192)
                            ->disk('public')
                            ->directory('products/posters')
                            ->visibility('public')
                            ->visible(fn (Get $get): bool => $get('type') === 'video'),
                        TextInput::make('alt')->label('Alt / опис'),
                        Toggle::make('is_active')->label('Показувати')->default(true),
                    ])
                    ->itemLabel(fn (array $state): string => ($state['type'] ?? 'image') === 'video'
                        ? 'Відео — '.basename($state['url'] ?? '')
                        : 'Фото — '.basename($state['url'] ?? ''))
                    ->orderColumn('position')
                    ->reorderableWithDragAndDrop()
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->columns(2)
                    ->columnSpanFull(),
                TextInput::make('seo_title'),
                TextInput::make('seo_description'),
                Repeater::make('variants')->relationship()->schema([
                    TextInput::make('sku')->required(),
                    TextInput::make('name')->required(),
                    TextInput::make('price_amount')
                        ->label('Ціна, грн')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->step(1)
                        ->suffix('₴')
                        ->formatStateUsing(fn ($state): int => (int) round(((int) $state) / 100))
                        ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100))
                        ->required(),
                    TextInput::make('stock_on_hand')->numeric()->required(),
                    Toggle::make('is_active')->default(true),
                ])->columns(5)->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('published_at'),
            ]);
    }
}
