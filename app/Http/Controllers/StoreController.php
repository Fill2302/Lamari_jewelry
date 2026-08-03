<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\HomepageSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function home(): Response
    {
        $homepage = HomepageSetting::query()->first();
        $productRelations = ['variants', 'media'];
        $newProducts = Product::where('is_active', true)
            ->with($productRelations)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
        $hitProducts = Product::where('is_active', true)
            ->where('catalog_badges', 'like', '%hit%')
            ->with($productRelations)
            ->orderBy('catalog_position')
            ->limit(8)
            ->get();

        if ($hitProducts->isEmpty()) {
            $hitProducts = Product::where('is_active', true)
                ->whereNotIn('id', $newProducts->pluck('id'))
                ->with($productRelations)
                ->orderBy('catalog_position')
                ->limit(8)
                ->get();
        }

        return Inertia::render('Home', [
            'categories' => Category::whereNull('parent_id')
                ->where('is_active', true)
                ->where('show_on_home', true)
                ->with('children')
                ->orderBy('position')
                ->orderBy('id')
                ->get(),
            'newProducts' => $newProducts,
            'hitProducts' => $hitProducts,
            'homepage' => $homepage,
        ]);
    }

    public function catalog(Request $request): Response
    {
        $selectedFilters = $this->selectedFilters($request);
        $baseQuery = Product::where('is_active', true);
        $searchQuery = mb_substr(trim((string) $request->input('q')), 0, 100);

        if ($searchQuery !== '') {
            $matchingProductIds = Product::where('is_active', true)
                ->with('variants:id,product_id,sku')
                ->get(['id', 'name'])
                ->filter(fn (Product $product): bool => mb_stripos($product->name, $searchQuery) !== false
                    || $product->variants->contains(fn ($variant): bool => mb_stripos($variant->sku, $searchQuery) !== false))
                ->pluck('id');

            $baseQuery->whereIn('id', $matchingProductIds);
        }

        $priceBounds = $this->priceBounds(clone $baseQuery);

        $products = $this->applyCatalogFilters(clone $baseQuery, $request, $selectedFilters, 'catalog_position')
            ->with('variants', 'media', 'attributeValues.attribute')
            ->paginate(24)
            ->withQueryString();

        $filters = Attribute::where('is_active', true)
            ->where('is_filterable', true)
            ->with(['values' => fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('products', fn ($products) => $products->where('is_active', true))])
            ->orderBy('position')
            ->get()
            ->filter(fn ($attribute) => $attribute->values->isNotEmpty())
            ->values();

        return Inertia::render('Category', [
            'category' => [
                'name' => 'Усі прикраси',
                'slug' => 'catalog',
                'description' => 'Повний каталог прикрас Lamari.',
                'children' => [],
            ],
            'categoryNavigation' => [
                'root' => null,
                'allHref' => '/catalog',
                'items' => Category::whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->get(['id', 'name', 'slug']),
            ],
            'products' => $products->items(),
            'productTotal' => $products->total(),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'lastPage' => $products->lastPage(),
                'prevUrl' => $products->previousPageUrl(),
                'nextUrl' => $products->nextPageUrl(),
                'pageUrls' => $products->getUrlRange(1, $products->lastPage()),
            ],
            'filters' => $filters,
            'selectedFilters' => $selectedFilters,
            'catalogControls' => $this->catalogControls($request, $priceBounds),
            'catalogUrl' => '/catalog',
            'searchQuery' => $searchQuery,
        ]);
    }

    public function category(Request $request, Category $category): Response
    {
        $category->load(['children' => fn ($query) => $query
            ->where('is_active', true)
            ->orderBy('position'), 'parent.children' => fn ($query) => $query
            ->where('is_active', true)
            ->orderBy('position')]);
        $navigationRoot = $category->parent ?: $category;
        $navigationItems = $category->parent
            ? $category->parent->children
            : $category->children;
        $selectedFilters = $this->selectedFilters($request);
        $baseQuery = Product::where('is_active', true);
        $baseQuery->whereHas('categories', fn ($categories) => $categories
            ->where('categories.id', $category->id));
        $priceBounds = $this->priceBounds(clone $baseQuery);

        $products = $this->applyCatalogFilters(
            clone $baseQuery,
            $request,
            $selectedFilters,
            'category_position',
            $category->id,
        )
            ->with('variants', 'media', 'attributeValues.attribute')
            ->paginate(24)
            ->withQueryString();

        $filters = Attribute::where('is_active', true)
            ->where('is_filterable', true)
            ->with(['values' => fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('products', function ($products) use ($category): void {
                    $products->where('is_active', true);
                    $products->whereHas('categories', fn ($categories) => $categories
                        ->where('categories.id', $category->id));
                })])
            ->orderBy('position')
            ->get()
            ->filter(fn ($attribute) => $attribute->values->isNotEmpty())
            ->values();

        return Inertia::render('Category', [
            'category' => $category,
            'categoryNavigation' => [
                'root' => $navigationRoot->only(['id', 'name', 'slug']),
                'allHref' => '/catalog',
                'items' => $navigationItems,
            ],
            'products' => $products->items(),
            'productTotal' => $products->total(),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'lastPage' => $products->lastPage(),
                'prevUrl' => $products->previousPageUrl(),
                'nextUrl' => $products->nextPageUrl(),
                'pageUrls' => $products->getUrlRange(1, $products->lastPage()),
            ],
            'filters' => $filters,
            'selectedFilters' => $selectedFilters,
            'catalogControls' => $this->catalogControls($request, $priceBounds),
        ]);
    }

    private function selectedFilters(Request $request): array
    {
        return collect($request->input('filters', []))
            ->map(fn ($values) => array_values(array_filter((array) $values)))
            ->filter()
            ->all();
    }

    private function applyCatalogFilters(
        Builder $query,
        Request $request,
        array $selectedFilters,
        string $manualPositionColumn,
        ?int $membershipCategoryId = null,
    ): Builder {
        foreach ($selectedFilters as $attributeSlug => $valueSlugs) {
            $query->whereHas('attributeValues', fn ($values) => $values
                ->whereIn('attribute_values.slug', $valueSlugs)
                ->whereHas('attribute', fn ($attribute) => $attribute->where('slug', $attributeSlug)));
        }

        $priceFrom = max(0, (int) $request->input('price_from', 0)) * 100;
        $priceTo = max(0, (int) $request->input('price_to', 0)) * 100;
        if ($priceFrom) {
            $query->whereHas('variants', fn ($variants) => $variants->where('price_amount', '>=', $priceFrom));
        }
        if ($priceTo) {
            $query->whereHas('variants', fn ($variants) => $variants->where('price_amount', '<=', $priceTo));
        }

        if ($request->input('availability') === 'in_stock') {
            $query->whereHas('variants', fn ($variants) => $variants->whereColumn('stock_on_hand', '>', 'stock_reserved'));
        } elseif ($request->input('availability') === 'preorder') {
            $query->whereDoesntHave('variants', fn ($variants) => $variants->whereColumn('stock_on_hand', '>', 'stock_reserved'));
        }

        return match ($request->input('sort')) {
            'price_asc' => $query->orderBy(
                ProductVariant::selectRaw('MIN(price_amount)')
                    ->whereColumn('product_id', 'products.id')
            ),
            'price_desc' => $query->orderByDesc(
                ProductVariant::selectRaw('MAX(price_amount)')
                    ->whereColumn('product_id', 'products.id')
            ),
            'newest' => $query->latest('products.id'),
            default => $query
                ->orderBy($membershipCategoryId
                    ? DB::table('category_product')
                        ->select('position')
                        ->whereColumn('product_id', 'products.id')
                        ->where('category_id', $membershipCategoryId)
                        ->limit(1)
                    : "products.{$manualPositionColumn}")
                ->orderByDesc('products.id'),
        };
    }

    private function priceBounds(Builder $query): array
    {
        $prices = ProductVariant::query()
            ->whereHas('product', fn ($products) => $products->whereIn('id', $query->select('id')))
            ->selectRaw('MIN(price_amount) as min_price, MAX(price_amount) as max_price')
            ->first();

        return [
            'min' => (int) floor(($prices->min_price ?? 0) / 100),
            'max' => (int) ceil(($prices->max_price ?? 0) / 100),
        ];
    }

    private function catalogControls(Request $request, array $priceBounds): array
    {
        return [
            'priceMin' => $priceBounds['min'],
            'priceMax' => $priceBounds['max'],
            'priceFrom' => (int) $request->input('price_from', $priceBounds['min']),
            'priceTo' => (int) $request->input('price_to', $priceBounds['max']),
            'availability' => $request->input('availability'),
            'sort' => $request->input('sort', 'manual'),
        ];
    }

    public function product(Product $product): Response
    {
        $product->load('category.parent', 'variants', 'media');

        $recommendedProducts = $product->relatedProducts()
            ->wherePivot('type', 'complete_look')
            ->where('products.is_active', true)
            ->whereHas('variants', fn ($variants) => $variants
                ->where('is_active', true)
                ->whereColumn('stock_on_hand', '>', 'stock_reserved'))
            ->with(['variants', 'media'])
            ->get();

        return Inertia::render('Product', [
            'product' => $product,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }
}
