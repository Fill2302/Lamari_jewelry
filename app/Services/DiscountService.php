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

        return (int) $this->activeDiscounts()
            ->where('mode', 'standard')
            ->filter(fn (Discount $discount): bool => $this->matches($discount, $variant))
            ->max('percentage');
    }

    /** @param Collection<int, ProductVariant> $variants */
    public function percentagesForCart(Collection $variants, array $quantities): array
    {
        $units = $variants->flatMap(function (ProductVariant $variant) use ($quantities): array {
            return array_fill(0, (int) ($quantities[$variant->id] ?? 0), [
                'variant_id' => $variant->id,
                'price' => (int) $variant->getRawOriginal('price_amount'),
            ]);
        })->values();

        $percentages = $units->map(function (array $unit) use ($variants): int {
            $variant = $variants->firstWhere('id', $unit['variant_id']);

            return $variant ? $this->percentageFor($variant) : 0;
        })->all();

        foreach ($this->activeDiscounts()->where('mode', 'quantity') as $discount) {
            $eligibleIndexes = $units
                ->keys()
                ->filter(fn (int $index): bool => ($variant = $variants->firstWhere('id', $units[$index]['variant_id']))
                    && $this->matches($discount, $variant))
                ->sortBy(fn (int $index): int => $units[$index]['price'])
                ->values();

            $rule = collect($discount->quantity_rules ?? [])
                ->filter(fn (array $rule): bool => (int) ($rule['min_quantity'] ?? 0) <= $eligibleIndexes->count())
                ->sortByDesc(fn (array $rule): int => (int) $rule['min_quantity'])
                ->first();

            if (! $rule) {
                continue;
            }

            $percentage = (int) ($rule['percentage'] ?? 0);
            $targets = ($rule['apply_to'] ?? 'all') === 'position'
                ? $eligibleIndexes->slice(max(0, (int) ($rule['position'] ?? 1) - 1), 1)
                : $eligibleIndexes;

            foreach ($targets as $index) {
                $percentages[$index] = max($percentages[$index], $percentage);
            }
        }

        $result = [];
        foreach ($units as $index => $unit) {
            $result[$unit['variant_id']][] = $percentages[$index];
        }

        return $result;
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

    private function matches(Discount $discount, ProductVariant $variant): bool
    {
        $product = $variant->relationLoaded('product') ? $variant->product : $variant->product()->first();
        if (! $product) {
            return false;
        }

        $membershipIds = $product->relationLoaded('categories')
            ? $product->categories->pluck('id')
            : $product->categories()->pluck('categories.id');
        $membershipIds->push((int) $product->category_id);
        $categoryIds = $membershipIds
            ->flatMap(fn ($categoryId) => $this->categoryLineage((int) $categoryId))
            ->unique()
            ->values()
            ->all();

        return match ($discount->scope) {
            'all' => true,
            'product' => (int) $discount->product_id === (int) $product->id
                || $discount->products->contains('id', $product->id),
            'category' => in_array((int) $discount->category_id, $categoryIds, true)
                || $discount->categories->pluck('id')->intersect($categoryIds)->isNotEmpty(),
            default => false,
        };
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
