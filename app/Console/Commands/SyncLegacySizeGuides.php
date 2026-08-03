<?php

namespace App\Console\Commands;

use App\Models\Product;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Throwable;

class SyncLegacySizeGuides extends Command
{
    protected $signature = 'catalog:sync-size-guides';

    protected $description = 'Copy product size-guide labels from the legacy storefront';

    public function handle(): int
    {
        $products = Product::query()
            ->whereNotNull('legacy_source_url')
            ->get(['id', 'legacy_source_id', 'legacy_source_url']);
        $failed = 0;

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products->chunk(12) as $chunk) {
            $responses = Http::pool(fn (Pool $pool): array => $chunk->map(
                fn (Product $product) => $pool->as((string) $product->id)
                    ->withHeaders(['User-Agent' => 'Lamari size guide sync/1.0'])
                    ->timeout(25)
                    ->retry(2, 400)
                    ->get($product->legacy_source_url)
            )->all());

            foreach ($chunk as $product) {
                try {
                    $response = $responses[(string) $product->id] ?? null;
                    if (! $response?->successful()) {
                        throw new \RuntimeException('HTTP failure');
                    }

                    $product->update($this->sizeGuideData($response->body()));
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                }
                $bar->advance();
            }
        }

        $bar->finish();

        $this->newLine(2);
        $this->info('Synced: '.($products->count() - $failed).'; failed: '.$failed);

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @return array{size_guide_label: ?string, size_guide_type: ?string} */
    private function sizeGuideData(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);
        $node = $xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," choosesize ")]')?->item(0);
        $label = trim(preg_replace('/\s+/u', ' ', html_entity_decode($node?->textContent ?? '')));
        $hasGuideLink = $xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," sizegroup ")]//a[@data-bs-target="#size_group"]')?->length > 0;
        $heading = $hasGuideLink
            ? trim($xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," sizebody ")]//div[contains(concat(" ",normalize-space(@class)," ")," charcontainer ") and contains(concat(" ",normalize-space(@class)," ")," active ")]//span[contains(concat(" ",normalize-space(@class)," ")," charsnames ")]')?->item(0)?->textContent ?? '')
            : '';
        $type = match (true) {
            preg_match('/браслет/iu', $heading) === 1 => 'bracelet',
            preg_match('/кільц|каблуч/iu', $heading) === 1 => 'ring',
            preg_match('/кольє|ланцюж|чокер/iu', $heading) === 1 => 'necklace',
            default => null,
        };

        return [
            'size_guide_label' => $label !== '' ? $label : null,
            'size_guide_type' => $type,
        ];
    }
}
