<?php

namespace App\Console\Commands;

use App\Models\Product;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Throwable;

class SyncLegacyCatalogBadges extends Command
{
    protected $signature = 'catalog:sync-legacy-badges {--dry-run : Read without changing the database}';

    protected $description = 'Sync catalog badges and comparison prices from visible legacy products';

    public function handle(): int
    {
        $products = Product::query()
            ->where('is_active', true)
            ->whereNotNull('legacy_source_url')
            ->get(['id', 'legacy_source_id', 'legacy_source_url']);
        $stats = ['updated' => 0, 'badged' => 0, 'discounted' => 0, 'failed' => 0];
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products->chunk(12) as $chunk) {
            $responses = Http::pool(fn (Pool $pool): array => $chunk
                ->mapWithKeys(fn (Product $product): array => [
                    (string) $product->id => $pool->as((string) $product->id)
                        ->withHeaders(['User-Agent' => 'Lamari catalog migration/1.0'])
                        ->timeout(25)
                        ->retry(2, 300)
                        ->get($product->legacy_source_url),
                ])
                ->all());

            foreach ($chunk as $product) {
                try {
                    $response = $responses[(string) $product->id] ?? null;
                    if (! $response?->successful()) {
                        throw new \RuntimeException("HTTP failure for legacy product {$product->legacy_source_id}");
                    }

                    $data = $this->parse($response->body());
                    if (! $this->option('dry-run')) {
                        $product->update([
                            'catalog_badges' => $data['badges'] ?: null,
                            'compare_at_price_amount' => $data['compare_at_price_amount'],
                        ]);
                    }

                    $stats['updated']++;
                    $stats['badged'] += $data['badges'] !== [] ? 1 : 0;
                    $stats['discounted'] += $data['compare_at_price_amount'] !== null ? 1 : 0;
                } catch (Throwable $exception) {
                    report($exception);
                    $stats['failed']++;
                    $this->newLine();
                    $this->error("#{$product->legacy_source_id}: {$exception->getMessage()}");
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(['Updated', 'With badges', 'Discounted', 'Failed'], [[
            $stats['updated'], $stats['badged'], $stats['discounted'], $stats['failed'],
        ]]);

        return $stats['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @return array{badges: list<array{label: string, type: string}>, compare_at_price_amount: ?int} */
    private function parse(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);
        $badges = [];

        foreach ($xpath->query('(//div[contains(concat(" ",normalize-space(@class)," ")," badgecontainer ")])[1]//*[contains(concat(" ",normalize-space(@class)," ")," mybadge ")]') ?: [] as $node) {
            $label = trim(preg_replace('/\s+/', ' ', $node->textContent));
            if ($label === '') {
                continue;
            }
            $type = str_starts_with($label, '-') ? 'sale' : (mb_strtolower($label) === 'new' ? 'new' : 'hit');
            $badges[] = ['label' => $label, 'type' => $type];
        }

        $comparePriceText = trim($xpath->query('//*[@id="visprice2"]')?->item(0)?->textContent ?? '');
        $comparePrice = (int) preg_replace('/\D+/', '', $comparePriceText);

        return [
            'badges' => $badges,
            'compare_at_price_amount' => $comparePrice > 0 ? $comparePrice * 100 : null,
        ];
    }
}
