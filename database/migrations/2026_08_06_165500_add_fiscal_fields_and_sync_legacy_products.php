<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('receipt_name')->nullable()->after('name');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->string('receipt_name')->nullable()->after('name');
        });

        $path = database_path('data/legacy-product-fiscal.tsv');
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Cannot open legacy fiscal data: {$path}");
        }

        fgetcsv($handle, separator: "\t");

        while (($row = fgetcsv($handle, separator: "\t")) !== false) {
            [$legacyId, $article, $receiptName, $group] = array_pad($row, 4, null);

            $values = [
                'receipt_name' => trim((string) $receiptName),
                'payment_destination' => trim((string) $group) === '2 группа' ? 'mono' : 'privat',
                'updated_at' => now(),
            ];

            DB::table('products')
                ->where('legacy_source_id', (int) $legacyId)
                ->update($values);

            // A few products were recreated manually before legacy IDs were added.
            // Match them by their original article embedded in variant SKUs.
            if (trim((string) $article) !== '') {
                $articleVariants = array_unique([
                    trim((string) $article),
                    strtr(trim((string) $article), ['К' => 'K']),
                ]);
                $productIds = DB::table('product_variants')
                    ->where(function ($query) use ($articleVariants): void {
                        foreach ($articleVariants as $candidate) {
                            $query->orWhere('sku', 'like', $candidate.'-%');
                        }
                    })
                    ->pluck('product_id');

                DB::table('products')
                    ->whereIn('id', $productIds)
                    ->whereNull('legacy_source_id')
                    ->whereNull('receipt_name')
                    ->update($values);
            }
        }

        fclose($handle);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('receipt_name');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('receipt_name');
        });
    }
};
