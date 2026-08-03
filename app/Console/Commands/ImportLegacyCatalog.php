<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImportLegacyCatalog extends Command
{
    protected $signature = 'catalog:import-legacy
        {--limit=0 : Import only the first N products}
        {--dry-run : Read and validate without changing the database}
        {--without-images : Keep remote image URLs instead of downloading optimized thumbnails}';

    protected $description = 'Import currently visible products from lamari.jewelry';

    private const BASE = 'https://lamari.jewelry';

    /** Product 930 was already recreated and customized manually in staging. */
    private const SKIP_IDS = [199, 200, 240, 875, 930];

    public function handle(): int
    {
        $urls = $this->sitemapProductUrls();
        $urls = array_values(array_filter($urls, fn (string $url): bool => ! in_array($this->sourceId($url), self::SKIP_IDS, true)));

        if (($limit = (int) $this->option('limit')) > 0) {
            $urls = array_slice($urls, 0, $limit);
        }

        $this->info('Import candidates: '.count($urls));
        $stats = ['created' => 0, 'updated' => 0, 'failed' => 0, 'images' => 0];
        $bar = $this->output->createProgressBar(count($urls));
        $bar->start();

        foreach (array_chunk($urls, 8) as $chunk) {
            $responses = Http::pool(fn (Pool $pool): array => array_map(
                fn (string $url) => $pool->as((string) $this->sourceId($url))
                    ->withHeaders(['User-Agent' => 'Lamari catalog migration/1.0'])
                    ->timeout(25)
                    ->retry(2, 400)
                    ->get($url),
                $chunk
            ));

            foreach ($chunk as $url) {
                $sourceId = $this->sourceId($url);

                try {
                    $response = $responses[(string) $sourceId] ?? null;
                    if (! $response?->successful()) {
                        throw new \RuntimeException("HTTP failure for {$url}");
                    }

                    $data = $this->parseProduct($url, $response->body());
                    if ($this->option('dry-run')) {
                        $stats['created']++;
                    } else {
                        $result = $this->persist($data);
                        $stats[$result['status']]++;
                        $stats['images'] += $result['images'];
                    }
                } catch (Throwable $exception) {
                    report($exception);
                    $stats['failed']++;
                    $this->newLine();
                    $this->error("#{$sourceId}: {$exception->getMessage()}");
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(['Created', 'Updated', 'Failed', 'Downloaded images'], [[
            $stats['created'], $stats['updated'], $stats['failed'], $stats['images'],
        ]]);

        return $stats['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @return list<string> */
    private function sitemapProductUrls(): array
    {
        $xml = Http::withHeaders(['User-Agent' => 'Lamari catalog migration/1.0'])
            ->timeout(30)
            ->retry(3, 500)
            ->get(self::BASE.'/sitemap.php')
            ->throw()
            ->body();

        preg_match_all('~<loc>(https://lamari\\.jewelry/shop/tov/[^<]+)</loc>~u', $xml, $matches);

        return array_values(array_unique(array_map(
            fn (string $url): string => html_entity_decode($url),
            $matches[1] ?? []
        )));
    }

    private function sourceId(string $url): int
    {
        preg_match('~/shop/tov/(\\d+)/~', $url, $match);

        return (int) ($match[1] ?? 0);
    }

    /** @return array<string, mixed> */
    private function parseProduct(string $url, string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);
        $sourceId = $this->sourceId($url);
        $name = $this->text($xpath, '//h1[contains(concat(" ",normalize-space(@class)," ")," tov ")]');
        $price = (int) preg_replace('/\\D+/', '', $this->text($xpath, '//*[@id="visprice"]'));
        $article = trim((string) preg_replace('/^артикул\\s*/ui', '', $this->text($xpath, '//*[contains(@class,"articul")]')));

        if ($name === '' || $price <= 0) {
            throw new \RuntimeException('Missing product name or price');
        }

        $categoryLinks = $xpath->query('//div[contains(@class,"bredcrumbs")]//a[contains(@href,"/shop/cat/")]');
        $categoryNames = [];
        foreach ($categoryLinks ?: [] as $link) {
            $categoryNames[] = trim($link->textContent);
        }

        $images = [];
        foreach ($xpath->query('//a[contains(@class,"pitovitem")]/@href') ?: [] as $node) {
            $path = trim($node->nodeValue);
            if (preg_match('~^/media/articles/[^/]+\\.(?:jpe?g|png|webp)$~i', $path)) {
                $images[] = self::BASE.$path;
            }
        }
        $images = array_values(array_unique($images));

        $variants = [];
        foreach ($xpath->query('//a[contains(@class,"roundedsize")]') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $variantName = trim(preg_replace('/\\s+/', ' ', $node->textContent));
            preg_match('~choosemychar\\([^,]+,[^,]+,\\s*(-?\\d+)~', $node->getAttribute('onclick'), $match);
            $variants[] = ['name' => $variantName, 'price' => $price + (int) ($match[1] ?? 0)];
        }
        if ($variants === []) {
            $variants[] = ['name' => 'Стандартний', 'price' => $price];
        }

        $characteristics = [];
        $characteristicsNode = $this->accordion($xpath, 'Характеристики');
        if ($characteristicsNode) {
            foreach ((new DOMXPath($dom))->query('.//tr', $characteristicsNode) ?: [] as $row) {
                $cells = (new DOMXPath($dom))->query('./td', $row);
                if ($cells && $cells->length >= 2) {
                    $key = trim(preg_replace('/\\s+/', ' ', $cells->item(0)->textContent));
                    $value = trim(preg_replace('/\\s+/', ' ', $cells->item(1)->textContent));
                    if ($key !== '' && $value !== '') {
                        $characteristics[$key] = $value;
                    }
                }
            }
        }

        $description = $this->cleanText($this->accordion($xpath, 'Опис товару')?->textContent ?? '');
        $packaging = $this->cleanText($this->accordion($xpath, 'Упаковка')?->textContent ?? '');
        $delivery = $this->cleanText($this->accordion($xpath, 'Доставка та оплата')?->textContent ?? '');
        $sizeGuideLabel = $this->text($xpath, '//div[contains(concat(" ",normalize-space(@class)," ")," choosesize ")]') ?: null;
        $hasSizeGuide = $xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," sizegroup ")]//a[@data-bs-target="#size_group"]')?->length > 0;
        $sizeGuideHeading = $hasSizeGuide
            ? $this->text($xpath, '//div[contains(concat(" ",normalize-space(@class)," ")," sizebody ")]//div[contains(concat(" ",normalize-space(@class)," ")," charcontainer ") and contains(concat(" ",normalize-space(@class)," ")," active ")]//span[contains(concat(" ",normalize-space(@class)," ")," charsnames ")]')
            : '';
        $sizeGuideType = match (true) {
            preg_match('/браслет/iu', $sizeGuideHeading) === 1 => 'bracelet',
            preg_match('/кільц|каблуч/iu', $sizeGuideHeading) === 1 => 'ring',
            preg_match('/кольє|ланцюж|чокер/iu', $sizeGuideHeading) === 1 => 'necklace',
            default => null,
        };
        $slug = trim(basename((string) parse_url($url, PHP_URL_PATH)));

        return compact(
            'sourceId', 'url', 'name', 'slug', 'price', 'article', 'categoryNames',
            'images', 'variants', 'characteristics', 'description', 'packaging', 'delivery', 'sizeGuideLabel', 'sizeGuideType'
        );
    }

    /** @param array<string, mixed> $data
     *  @return array{status: string, images: int}
     */
    private function persist(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $category = $this->resolveCategory($data['categoryNames']);
            $existing = Product::where('legacy_source_id', $data['sourceId'])->first();
            $slug = $existing?->slug ?: $this->uniqueSlug($data['slug'], (int) $data['sourceId']);
            $localImages = $this->storeImages((int) $data['sourceId'], $data['images']);

            $product = Product::updateOrCreate(
                ['legacy_source_id' => $data['sourceId']],
                [
                    'legacy_source_url' => $data['url'],
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'] ?: $data['name'],
                    'material' => $data['characteristics']['Матеріал кольє']
                        ?? $data['characteristics']['Матеріал']
                        ?? null,
                    'characteristics' => $data['characteristics'],
                    'packaging_text' => $data['packaging'] ?: null,
                    'delivery_payment_text' => $data['delivery'] ?: null,
                    'size_guide_label' => $data['sizeGuideLabel'],
                    'size_guide_type' => $data['sizeGuideType'],
                    'image_url' => $localImages[0] ?? null,
                    'seo_title' => $data['name'].' — Lamari Jewelry',
                    'seo_description' => Str::limit($data['description'] ?: $data['name'], 155, ''),
                    'is_active' => true,
                    'published_at' => now(),
                ]
            );

            // Keep manually uploaded videos and their chosen gallery positions
            // when refreshing legacy photos.
            $videoPositions = $product->media()
                ->where('type', 'video')
                ->pluck('position')
                ->map(fn ($position) => (int) $position)
                ->all();

            $product->media()->where('type', '!=', 'video')->delete();
            $imagePosition = 0;
            foreach ($localImages as $path) {
                while (in_array($imagePosition, $videoPositions, true)) {
                    $imagePosition++;
                }

                $product->media()->create([
                    'type' => 'image',
                    'url' => $path,
                    'alt' => $data['name'],
                    'position' => $imagePosition++,
                    'is_active' => true,
                ]);
            }

            $product->variants()->delete();
            foreach ($data['variants'] as $position => $variant) {
                $skuBase = $data['article'] ?: 'LAM-'.$data['sourceId'];
                $suffix = preg_replace('/\\D+/', '', $variant['name']) ?: (string) ($position + 1);
                $product->variants()->create([
                    'sku' => $this->uniqueSku($skuBase.'-'.$suffix, (int) $data['sourceId']),
                    'name' => $variant['name'],
                    'price_amount' => (int) $variant['price'] * 100,
                    'currency' => 'UAH',
                    'stock_on_hand' => 10,
                    'stock_reserved' => 0,
                    'is_active' => true,
                ]);
            }

            return ['status' => $existing ? 'updated' : 'created', 'images' => count($localImages)];
        });
    }

    /** @param list<string> $names */
    private function resolveCategory(array $names): Category
    {
        $main = $names[0] ?? 'Кольє';
        $child = $names[1] ?? null;
        $mainSlugs = [
            'Кольє' => 'necklaces', 'Чокери' => 'chokers', 'Сережки' => 'earrings',
            'Ланцюжки' => 'chains', 'Браслети' => 'bracelets', 'Анклети' => 'anklets',
            'Булавки' => 'pins', 'Каблучки' => 'rings', 'Комплекти' => 'sets',
            'Sale' => 'sale', 'SALE' => 'sale', 'Літня колекція' => 'summer',
        ];
        $parent = Category::firstOrCreate(
            ['slug' => $mainSlugs[$main] ?? 'legacy-'.Str::slug($main)],
            ['name' => $main, 'is_active' => true]
        );

        if (! $child || $child === $main) {
            return $parent;
        }

        return Category::firstOrCreate(
            ['parent_id' => $parent->id, 'name' => $child],
            ['slug' => $parent->slug.'-'.Str::slug($child), 'is_active' => true]
        );
    }

    /** @param list<string> $urls
     *  @return list<string>
     */
    private function storeImages(int $sourceId, array $urls): array
    {
        if ($this->option('without-images')) {
            return $urls;
        }

        $stored = [];
        foreach ($urls as $position => $url) {
            $thumbnailUrl = str_replace('/media/articles/', '/media/articles/thumb/', $url);
            $response = Http::withHeaders(['User-Agent' => 'Lamari catalog migration/1.0'])
                ->timeout(25)
                ->retry(2, 300)
                ->get($thumbnailUrl);
            if (! $response->successful()) {
                continue;
            }
            $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
            $path = "products/imported/{$sourceId}-".($position + 1).".{$extension}";
            Storage::disk('public')->put($path, $response->body());
            $stored[] = $path;
        }

        return $stored;
    }

    private function uniqueSlug(string $slug, int $sourceId): string
    {
        return Product::where('slug', $slug)->exists() ? "{$slug}-{$sourceId}" : $slug;
    }

    private function uniqueSku(string $sku, int $sourceId): string
    {
        return \App\Models\ProductVariant::where('sku', $sku)->exists() ? "{$sku}-{$sourceId}" : $sku;
    }

    private function text(DOMXPath $xpath, string $query): string
    {
        $node = $xpath->query($query)?->item(0);

        return $this->cleanText($node?->textContent ?? '');
    }

    private function accordion(DOMXPath $xpath, string $label): ?DOMElement
    {
        $query = sprintf(
            '//div[contains(@class,"charcontainer")][.//span[contains(@class,"charsnames") and normalize-space()="%s"]]//div[contains(@class,"charcontent")]',
            $label
        );
        $node = $xpath->query($query)?->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private function cleanText(string $value): string
    {
        return trim(preg_replace('/\\s+/u', ' ', html_entity_decode($value)));
    }
}
