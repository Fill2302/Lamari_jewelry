<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_product', function (Blueprint $table) {
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['discount_id', 'product_id']);
        });

        Schema::create('category_discount', function (Blueprint $table) {
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['discount_id', 'category_id']);
        });

        DB::table('discounts')->whereNotNull('product_id')->orderBy('id')->each(function ($discount): void {
            DB::table('discount_product')->insertOrIgnore([
                'discount_id' => $discount->id,
                'product_id' => $discount->product_id,
            ]);
        });

        DB::table('discounts')->whereNotNull('category_id')->orderBy('id')->each(function ($discount): void {
            DB::table('category_discount')->insertOrIgnore([
                'discount_id' => $discount->id,
                'category_id' => $discount->category_id,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_discount');
        Schema::dropIfExists('discount_product');
    }
};
