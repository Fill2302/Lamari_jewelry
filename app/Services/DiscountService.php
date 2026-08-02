<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Discount;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class DiscountService
{
    private ?Collection $discounts = null;

    private ?array $categoryParents = null;

    public function percentageFor(ProductVariant $variant): int
    {
        $product = $variant->relationLoaded('product') ? $variant->product : $variant->product()->first();
        if (! $product) {
            return 0;
        }

        $categoryIds = $this->categoryLineage((int) $product->category_id);

        return (int) $this->activeDiscounts()
            ->filter(fn (Discount $discount): bool => match ($discount->scope) {
                'all' => true,
                'product' => (int) $discount->product_id === (int) $product->id
                    || $discount->products->contains('id', $product->id),
                'category' => in_array((int) $discount->category_id, $categoryIds, true)
                    || $discount->categories->pluck('id')->intersect($categoryIds)->isNotEmpty(),
                default => false,
            })
            ->max('percentage');
    }

    public function priceFor(ProductVariant $variant): int
    {
        $price = (int) $variant->getRawOriginal('price_amount');
        $percentage = $this->percentageFor($variant);

        return $percentage ? (int) round($price * (100 - $percentage) / 100) : $price;
    }

    private function activeDiscounts(): Collection
    {
        return $this->discounts ??= Discount::currentlyActive()->with(['products:id', 'categories:id'])->get();
    }

    private function categoryLineage(int $categoryId): array
    {
        $this->categoryParents ??= Category::pluck('parent_id', 'id')->map(fn ($id) => $id ? (int) $id : null)->all();
        $ids = [];
        while ($categoryId && ! in_array($categoryId, $ids, true)) {
            $ids[] = $categoryId;
            $categoryId = $this->categoryParents[$categoryId] ?? 0;
        }

        return $ids;
    }
}
