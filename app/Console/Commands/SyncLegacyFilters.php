<?php

namespace App\Console\Commands;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncLegacyFilters extends Command
{
    protected $signature = 'catalog:sync-legacy-filters';

    protected $description = 'Create the legacy Lamari filters and attach their values to imported products';

    public function handle(): int
    {
        $definitions = [
            'metal-color' => ['Колір', ['Золото', 'Срібло'], 'color'],
            'occasion' => ['Подія', ['На кожен день', 'Весілля', 'День народження', 'Побачення', 'Випускний', 'Вечірка', 'Подарунок'], 'select'],
            'stone-color' => ['Колір каменю', ['Білий', 'Зелений', 'Чорний', 'Червоний', 'Золотий', 'Срібний', 'Синій', 'Ліловий', 'Рожевий', 'Сірий', 'Сіро-блакитний', 'Блакитний'], 'color'],
            'jewelry-type' => ['Тип прикраси', [], 'select'],
        ];
        $colors = [
            'Золото' => '#c5a15d', 'Срібло' => '#c4c7c8', 'Білий' => '#f3f1ed',
            'Зелений' => '#5d7d65', 'Чорний' => '#262321', 'Червоний' => '#a93f36',
            'Золотий' => '#c5a15d', 'Срібний' => '#c4c7c8', 'Синій' => '#365f8d',
            'Ліловий' => '#9674a5', 'Рожевий' => '#d69aa6', 'Сірий' => '#8b8987',
            'Сіро-блакитний' => '#8295a1', 'Блакитний' => '#80adc4',
        ];

        $attributes = [];
        foreach ($definitions as $position => $definition) {
            [$name, $values, $type] = $definition;
            $attribute = Attribute::updateOrCreate(
                ['slug' => $position],
                ['name' => $name, 'type' => $type, 'position' => array_search($position, array_keys($definitions)), 'is_filterable' => true, 'is_active' => true],
            );
            foreach ($values as $valuePosition => $value) {
                AttributeValue::updateOrCreate(
                    ['attribute_id' => $attribute->id, 'slug' => Str::slug($value)],
                    ['value' => $value, 'color_hex' => $colors[$value] ?? null, 'position' => $valuePosition, 'is_active' => true],
                );
            }
            $attributes[$position] = $attribute->load('values');
        }

        Product::where('is_active', true)->with('category.parent')->chunkById(100, function ($products) use ($attributes) {
            foreach ($products as $product) {
                $text = Str::lower(implode(' ', [
                    $product->name,
                    $product->material,
                    json_encode($product->characteristics, JSON_UNESCAPED_UNICODE),
                ]));
                $valueIds = [];

                $valueIds[] = $this->valueId($attributes['occasion'], 'На кожен день');
                $valueIds[] = $this->valueId($attributes['occasion'], 'Подарунок');
                if (Str::contains($text, ['весіль', 'наречен'])) $valueIds[] = $this->valueId($attributes['occasion'], 'Весілля');
                if (Str::contains($text, ['вечір', 'святков'])) $valueIds[] = $this->valueId($attributes['occasion'], 'Вечірка');

                if (Str::contains($text, ['сріб', 'родій', 'сталь'])) {
                    $valueIds[] = $this->valueId($attributes['metal-color'], 'Срібло');
                } else {
                    $valueIds[] = $this->valueId($attributes['metal-color'], 'Золото');
                }

                $stoneMatchers = [
                    'Білий' => ['білий', 'білого', 'прозор', 'перл'],
                    'Зелений' => ['зелен', 'смарагд'],
                    'Чорний' => ['чорн', 'онікс'],
                    'Червоний' => ['червон', 'гранат'],
                    'Золотий' => ['золот'],
                    'Срібний' => ['сріб'],
                    'Синій' => ['синій', 'синього'],
                    'Ліловий' => ['лілов', 'фіолет', 'аметист'],
                    'Рожевий' => ['рожев'],
                    'Сірий' => ['сірий', 'сірого'],
                    'Сіро-блакитний' => ['сіро-блакит'],
                    'Блакитний' => ['блакит', 'аквамарин'],
                ];
                foreach ($stoneMatchers as $value => $needles) {
                    if (Str::contains($text, $needles)) $valueIds[] = $this->valueId($attributes['stone-color'], $value);
                }

                $category = $product->category;
                if ($category) {
                    $type = $category->parent ? $category->name : $category->name;
                    $typeValue = AttributeValue::firstOrCreate(
                        ['attribute_id' => $attributes['jewelry-type']->id, 'slug' => Str::slug($type)],
                        ['value' => $type, 'position' => $category->position ?? 0, 'is_active' => true],
                    );
                    $valueIds[] = $typeValue->id;
                }

                $product->attributeValues()->syncWithoutDetaching(array_values(array_unique(array_filter($valueIds))));
            }
        });

        $this->info('Legacy filters synchronized.');

        return self::SUCCESS;
    }

    private function valueId(Attribute $attribute, string $value): ?int
    {
        return $attribute->values->firstWhere('value', $value)?->id;
    }
}
