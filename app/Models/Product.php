<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(function (Product $product): void {
            if (! $product->category_id || ! \Schema::hasTable('category_product')) {
                return;
            }

            $category = Category::find($product->category_id);
            if (! $category) {
                return;
            }

            $ids = [$category->id];
            while ($category->parent_id) {
                $category = Category::find($category->parent_id);
                if (! $category) {
                    break;
                }
                $ids[] = $category->id;
            }

            $product->categories()->syncWithoutDetaching(array_fill_keys($ids, [
                'position' => $product->category_position ?? 1000,
            ]));
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'characteristics' => 'array',
            'catalog_badges' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('position');
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_relations',
            'product_id',
            'related_product_id'
        )->withPivot(['type', 'position'])->orderByPivot('position');
    }
}
