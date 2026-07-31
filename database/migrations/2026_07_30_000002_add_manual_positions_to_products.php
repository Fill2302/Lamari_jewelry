<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('catalog_position')->default(1000)->after('category_id');
            $table->unsignedInteger('category_position')->default(1000)->after('catalog_position');
            $table->index(['is_active', 'catalog_position']);
            $table->index(['category_id', 'is_active', 'category_position']);
        });

        $catalogPosition = 1;
        $categoryPositions = [];

        DB::table('products')
            ->orderByDesc('id')
            ->get(['id', 'category_id'])
            ->each(function (object $product) use (&$catalogPosition, &$categoryPositions): void {
                $categoryPositions[$product->category_id] = ($categoryPositions[$product->category_id] ?? 0) + 1;

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'catalog_position' => $catalogPosition++,
                        'category_position' => $categoryPositions[$product->category_id],
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'catalog_position']);
            $table->dropIndex(['category_id', 'is_active', 'category_position']);
            $table->dropColumn(['catalog_position', 'category_position']);
        });
    }
};
