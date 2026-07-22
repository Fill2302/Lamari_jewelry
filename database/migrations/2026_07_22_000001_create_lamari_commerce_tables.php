<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_entities', fn (Blueprint $t) => [$t->id(), $t->string('name'), $t->string('tax_id')->nullable(), $t->boolean('is_active')->default(true), $t->timestamps()]);
        Schema::create('merchant_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('legal_entity_id')->constrained();
            $t->string('provider');
            $t->string('code')->unique();
            $t->boolean('is_default')->default(false);
            $t->boolean('test_mode')->default(true);
            $t->json('selection_rules')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('seo_description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description');
            $t->string('material')->nullable();
            $t->string('image_url')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('seo_description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('sku')->unique();
            $t->string('name');
            $t->unsignedBigInteger('price_amount');
            $t->char('currency', 3)->default('UAH');
            $t->unsignedInteger('stock_on_hand')->default(0);
            $t->unsignedInteger('stock_reserved')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('merchant_account_id')->constrained();
            $t->foreignId('legal_entity_id')->constrained();
            $t->string('email');
            $t->string('phone');
            $t->string('customer_name');
            $t->json('shipping_address');
            $t->string('status')->default('pending_payment');
            $t->string('payment_status')->default('pending');
            $t->unsignedBigInteger('subtotal_amount');
            $t->unsignedBigInteger('total_amount');
            $t->char('currency', 3)->default('UAH');
            $t->timestamps();
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $t->string('sku');
            $t->string('name');
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_price_amount');
            $t->unsignedBigInteger('total_amount');
            $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained();
            $t->foreignId('merchant_account_id')->constrained();
            $t->foreignId('legal_entity_id')->constrained();
            $t->string('provider');
            $t->string('provider_payment_id')->nullable();
            $t->string('status')->default('pending');
            $t->unsignedBigInteger('amount');
            $t->char('currency', 3)->default('UAH');
            $t->string('idempotency_key')->unique();
            $t->json('payload')->nullable();
            $t->timestamps();
        });
        Schema::create('webhook_events', function (Blueprint $t) {
            $t->id();
            $t->string('provider');
            $t->string('external_id');
            $t->string('event_type');
            $t->boolean('signature_valid');
            $t->json('payload');
            $t->string('status')->default('received');
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
            $t->unique(['provider', 'external_id']);
        });
    }

    public function down(): void
    {
        foreach (['webhook_events', 'payments', 'order_items', 'orders', 'product_variants', 'products', 'categories', 'merchant_accounts', 'legal_entities'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
