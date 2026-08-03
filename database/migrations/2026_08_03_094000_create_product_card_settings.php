<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_card_settings', function (Blueprint $table) {
            $table->id();
            $table->string('characteristics_title')->default('Характеристики');
            $table->string('description_title')->default('Опис товару');
            $table->string('packaging_title')->default('Упаковка');
            $table->string('care_title')->default('Догляд');
            $table->string('delivery_payment_title')->default('Доставка та оплата');
            $table->text('delivery_text');
            $table->text('payment_text');
            $table->string('warranty_question');
            $table->text('warranty_answer');
            $table->string('returns_question');
            $table->text('returns_answer');
            $table->string('water_question');
            $table->text('water_answer');
            $table->string('tarnish_question');
            $table->text('tarnish_answer');
            $table->timestamps();
        });

        DB::table('product_card_settings')->insert([
            'characteristics_title' => 'Характеристики',
            'description_title' => 'Опис товару',
            'packaging_title' => 'Упаковка',
            'care_title' => 'Догляд',
            'delivery_payment_title' => 'Доставка та оплата',
            'delivery_text' => 'Доставка по Україні здійснюється Новою поштою. Також доступна міжнародна доставка. Точний спосіб, вартість і термін доставки будуть зазначені під час оформлення замовлення.',
            'payment_text' => 'Замовлення можна оплатити банківською карткою, через Apple Pay або Google Pay. Також доступні передплата та оплата частинами.',
            'warranty_question' => 'Яка гарантія на вироби?',
            'warranty_answer' => 'На всі прикраси LAMARI діє гарантія 1 місяць. Якщо протягом цього часу виявиться виробничий дефект, ми безкоштовно відремонтуємо або замінимо виріб. Гарантія не поширюється на механічні пошкодження та пошкодження через недотримання рекомендацій із догляду.',
            'returns_question' => 'Чи можу я обміняти або повернути товар?',
            'returns_answer' => 'Так, ви можете обміняти товар на інший або повернути його протягом 14 днів із моменту отримання.',
            'water_question' => 'Чи можна мочити прикраси?',
            'water_answer' => 'Прикраси з ювелірної сталі можна мочити та носити не знімаючи. Прикраси з покриттям золотом або родієм рекомендуємо знімати перед душем, морем чи басейном, щоб вони якомога довше зберігали свій початковий вигляд.',
            'tarnish_question' => 'Чи темніють прикраси?',
            'tarnish_answer' => 'Наші прикраси не темніють. За умови дотримання рекомендацій із догляду та правильного зберігання вони довго зберігатимуть свій початковий вигляд.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_card_settings');
    }
};
