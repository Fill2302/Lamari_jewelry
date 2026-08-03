<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(1000);
            $table->primary(['category_id', 'product_id']);
            $table->index(['category_id', 'position']);
        });

        DB::table('products')->orderBy('id')->each(function (object $product): void {
            DB::table('category_product')->insertOrIgnore([
                'category_id' => $product->category_id,
                'product_id' => $product->id,
                'position' => $product->category_position,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
