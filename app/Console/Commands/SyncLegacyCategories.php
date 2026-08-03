<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncLegacyCategories extends Command
{
    protected $signature = 'catalog:sync-legacy-categories {--dry-run : Inspect without changing the database}';

    protected $description = 'Mirror product category, subcategory, Sale and Summer collection memberships from lamari.jewelry';

    private const BASE = 'https://lamari.jewelry';

    /** @return list<array{name:string,slug:string,path:string,children:list<array{name:string,id:int}>}> */
    private function structure(): array
    {
        return [
            ['name' => 'Sale', 'slug' => 'sale', 'path' => '/shop/sales/', 'children' => []],
            ['name' => 'Літня колекція', 'slug' => 'summer', 'path' => '/shop/cat/42/litnya-kolekciya', 'children' => [
                ['name' => 'Чокери', 'id' => 51], ['name' => 'Кольє', 'id' => 52],
                ['name' => 'Ланцюжки', 'id' => 53], ['name' => 'Браслети', 'id' => 54],
                ['name' => 'Сережки', 'id' => 55], ['name' => 'Комплекти', 'id' => 56],
                ['name' => 'Булавки', 'id' => 61],
            ]],
            ['name' => 'Кольє', 'slug' => 'necklaces', 'path' => '/shop/cat/6/kolie', 'children' => [
                ['name' => 'З кришталю', 'id' => 48], ['name' => 'З перлами', 'id' => 7],
                ['name' => 'З ланцюжка', 'id' => 8], ['name' => 'З натуральним камінням', 'id' => 9],
            ]],
            ['name' => 'Чокери', 'slug' => 'chokers', 'path' => '/shop/cat/11/chokeri', 'children' => [
                ['name' => 'З кришталю', 'id' => 49], ['name' => 'З перлами', 'id' => 12],
                ['name' => 'З натуральним камінням', 'id' => 13], ['name' => 'Шнури', 'id' => 47],
                ['name' => 'З бісеру', 'id' => 14],
            ]],
            ['name' => 'Сережки', 'slug' => 'earrings', 'path' => '/shop/cat/15/serezhki', 'children' => [
                ['name' => 'З фіанітами', 'id' => 58], ['name' => 'З перлами', 'id' => 16],
                ['name' => 'З натуральним камінням', 'id' => 17], ['name' => 'Базові сережки', 'id' => 19],
            ]],
            ['name' => 'Ланцюжки', 'slug' => 'chains', 'path' => '/shop/cat/20/lancyuzhki', 'children' => [
                ['name' => 'Базові ланцюжки', 'id' => 21], ['name' => 'З перлинами', 'id' => 22],
                ['name' => 'З натуральним камінням', 'id' => 23],
            ]],
            ['name' => 'Браслети', 'slug' => 'bracelets', 'path' => '/shop/cat/24/brasleti', 'children' => [
                ['name' => 'З перлами', 'id' => 25], ['name' => 'З натуральним камінням', 'id' => 26],
                ['name' => 'З бісеру', 'id' => 27], ['name' => 'З ланцюжка', 'id' => 28],
                ['name' => 'З кришталю', 'id' => 50],
            ]],
            ['name' => 'Анклети', 'slug' => 'anklets', 'path' => '/shop/cat/29/ankleti', 'children' => [
                ['name' => 'З перлами', 'id' => 30], ['name' => 'З натуральним камінням', 'id' => 31],
                ['name' => 'З бісеру', 'id' => 32], ['name' => 'З ланцюжка', 'id' => 33],
            ]],
            ['name' => 'Булавки', 'slug' => 'pins', 'path' => '/shop/cat/60/bulavki', 'children' => []],
            ['name' => 'Каблучки', 'slug' => 'rings', 'path' => '/shop/cat/34/kabluchki', 'children' => [
                ['name' => 'Акцентні', 'id' => 57], ['name' => 'З перлами', 'id' => 35],
                ['name' => 'З натуральним камінням', 'id' => 36], ['name' => 'З ланцюжка', 'id' => 37],
            ]],
            ['name' => 'Комплекти', 'slug' => 'sets', 'path' => '/shop/cat/38/komplekti', 'children' => [
                ['name' => 'З кришталю', 'id' => 59], ['name' => 'З перлами', 'id' => 39],
                ['name' => 'З натуральним камінням', 'id' => 40], ['name' => 'З ланцюжка', 'id' => 41],
            ]],
        ];
    }

    public function handle(): int
    {
        $sourceToProduct = Product::whereNotNull('legacy_source_id')->pluck('id', 'legacy_source_id');
        $memberships = [];
        $rows = [];
        $missing = [];

        foreach ($this->structure() as $rootPosition => $definition) {
            $parent = $this->category($definition['name'], $definition['slug'], null, $rootPosition + 1);
            $this->collect($parent, $definition['path'], $sourceToProduct, $memberships, $missing, $rows);

            foreach ($definition['children'] as $childPosition => $child) {
                $category = $this->category(
                    $child['name'],
                    $definition['slug'].'-'.Str::slug($child['name']),
                    $parent->id,
                    $childPosition + 1,
                );
                $separator = str_contains($definition['path'], '?') ? '&' : '?';
                $this->collect(
                    $category,
                    $definition['path'].$separator.'cat='.$child['id'],
                    $sourceToProduct,
                    $memberships,
                    $missing,
                    $rows,
                );
            }
        }

        if (! $this->option('dry-run')) {
            DB::transaction(function () use ($memberships, $sourceToProduct): void {
                DB::table('category_product')->whereIn('product_id', $sourceToProduct->values())->delete();
                foreach (array_chunk($memberships, 500) as $chunk) {
                    DB::table('category_product')->insert($chunk);
                }
            });
        }

        $this->table(['Розділ / підрозділ', 'Товарів'], $rows);
        $this->info(sprintf(
            '%s %d exact memberships for %d products; %d source products are absent on staging.',
            $this->option('dry-run') ? 'Would sync' : 'Synced',
            count($memberships),
            collect($memberships)->pluck('product_id')->unique()->count(),
            count(array_unique($missing)),
        ));

        return self::SUCCESS;
    }

    private function category(string $name, string $slug, ?int $parentId, int $position): Category
    {
        if ($this->option('dry-run')) {
            return Category::firstOrNew(['parent_id' => $parentId, 'name' => $name], [
                'slug' => $slug, 'position' => $position, 'is_active' => true,
            ]);
        }

        $category = Category::firstOrCreate(
            ['parent_id' => $parentId, 'name' => $name],
            ['slug' => $slug, 'is_active' => true],
        );
        $category->update(['position' => $position, 'is_active' => true]);

        return $category;
    }

    private function collect(
        Category $category,
        string $path,
        $sourceToProduct,
        array &$memberships,
        array &$missing,
        array &$rows,
    ): void {
        $sourceIds = $this->productIds($path);
        $position = 0;
        foreach ($sourceIds as $sourceId) {
            $productId = $sourceToProduct->get($sourceId);
            if (! $productId) {
                $missing[] = $sourceId;

                continue;
            }
            $memberships[] = [
                'category_id' => $category->id,
                'product_id' => $productId,
                'position' => ++$position,
            ];
        }
        $rows[] = [str_repeat('  ', $category->parent_id ? 1 : 0).$category->name, $position];
    }

    /** @return list<int> */
    private function productIds(string $path): array
    {
        $first = $this->page($path);
        $lastPage = 1;
        if (preg_match_all('/[?&]page=(\d+)/', $first, $matches)) {
            $lastPage = max(array_map('intval', $matches[1]));
        }

        $ids = $this->extractProductIds($first);
        for ($page = 2; $page <= $lastPage; $page++) {
            $separator = str_contains($path, '?') ? '&' : '?';
            $ids = [...$ids, ...$this->extractProductIds($this->page($path.$separator.'page='.$page))];
        }

        return array_values(array_unique($ids));
    }

    private function page(string $path): string
    {
        return Http::withHeaders(['User-Agent' => 'Lamari category synchronization/1.0'])
            ->timeout(30)
            ->retry(3, 500)
            ->get(self::BASE.$path)
            ->throw()
            ->body();
    }

    /** @return list<int> */
    private function extractProductIds(string $html): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);
        $ids = [];
        foreach ($xpath->query('//a[contains(@href,"/shop/tov/")]/@href') ?: [] as $node) {
            if (preg_match('~/shop/tov/(\d+)/~', $node->nodeValue, $match)) {
                $ids[] = (int) $match[1];
            }
        }

        return array_values(array_unique($ids));
    }
}
