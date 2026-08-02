<?php

namespace App\Console\Commands;

use App\Models\Product;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class ImportLegacyProductRelations extends Command
{
    protected $signature = 'catalog:import-legacy-relations
        {--dry-run}
        {--offset=0 : Skip this many source products}
        {--limit=0 : Process at most this many source products}';

    protected $description = 'Import per-product Complete the look relations from lamari.jewelry';

    public function handle(): int
    {
        $products = Product::query()
            ->whereNotNull('legacy_source_id')
            ->whereNotNull('legacy_source_url')
            ->orderBy('id')
            ->skip((int) $this->option('offset'))
            ->when((int) $this->option('limit') > 0, fn ($query) => $query->take((int) $this->option('limit')))
            ->get(['id', 'legacy_source_id', 'legacy_source_url']);
        $sourceToId = Product::query()
            ->whereNotNull('legacy_source_id')
            ->pluck('id', 'legacy_source_id');
        $synced = $relations = $missing = $failed = 0;

        foreach ($products->chunk(12) as $chunk) {
            $responses = Http::pool(fn (Pool $pool): array => $chunk->mapWithKeys(fn (Product $product): array => [
                (string) $product->id => $pool->as((string) $product->id)
                    ->withHeaders(['User-Agent' => 'Lamari relations migration/1.0'])
                    ->timeout(30)
                    ->retry(2, 400)
                    ->get($product->legacy_source_url),
            ])->all());

            foreach ($chunk as $product) {
                $response = $responses[(string) $product->id] ?? null;
                if (! $response?->successful()) {
                    $failed++;
                    continue;
                }

                $sourceIds = $this->relationSourceIds($response->body());
                $sync = [];
                foreach ($sourceIds as $position => $sourceId) {
                    $relatedId = $sourceToId->get($sourceId);
                    if (! $relatedId || (int) $relatedId === $product->id) {
                        $missing++;
                        continue;
                    }
                    $sync[(int) $relatedId] = [
                        'type' => 'complete_look',
                        'position' => $position,
                    ];
                }

                if (! $this->option('dry-run')) {
                    $product->relatedProducts()->wherePivot('type', 'complete_look')->detach();
                    $product->relatedProducts()->attach($sync);
                }
                $synced++;
                $relations += count($sync);
            }
        }

        $this->table(['Products synced', 'Relations', 'Missing targets', 'Failed pages'], [[
            $synced, $relations, $missing, $failed,
        ]]);

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @return list<int> */
    private function relationSourceIds(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);
        $ids = [];

        foreach ($xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " obraz ")]//a[contains(@href,"/shop/tov/")]/@href') ?: [] as $node) {
            if (preg_match('~/shop/tov/(\d+)/~', $node->nodeValue, $match)) {
                $ids[] = (int) $match[1];
            }
        }

        return array_values(array_unique($ids));
    }
}
