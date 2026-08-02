<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('desktop_hero_image')->nullable();
            $table->string('mobile_hero_video')->nullable();
            $table->string('mobile_hero_poster')->nullable();
            $table->string('hero_link')->default('/catalog');
            $table->string('ticker_text')->default('БЕЗКОШТОВНЕ БРЕНДОВАНЕ ПАКУВАННЯ');
            $table->boolean('show_new_products')->default(true);
            $table->string('new_products_title')->default('Новинки');
            $table->boolean('show_hit_products')->default(true);
            $table->string('hit_products_title')->default('Хіти продажів');
            $table->json('faq_items')->nullable();
            $table->string('instagram_title')->default('Ви і Lamari Jewelry');
            $table->text('instagram_text')->nullable();
            $table->string('instagram_url')->nullable();
            $table->json('instagram_images')->nullable();
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_on_home')->default(false)->after('is_active');
        });

        DB::table('categories')->whereIn('slug', [
            'necklaces', 'chokers', 'earrings', 'chains', 'bracelets',
            'anklets', 'rings', 'sets', 'summer', 'pins',
        ])->update(['show_on_home' => true]);

        DB::table('homepage_settings')->insert([
            'desktop_hero_image' => '/images/home/summer-collection-desktop.jpg?v=2',
            'mobile_hero_video' => '/images/home/hero-video.mp4',
            'mobile_hero_poster' => '/images/home/hero-video-first-frame.webp',
            'hero_link' => '/catalog',
            'ticker_text' => 'БЕЗКОШТОВНЕ БРЕНДОВАНЕ ПАКУВАННЯ',
            'show_new_products' => true,
            'new_products_title' => 'Новинки',
            'show_hit_products' => true,
            'hit_products_title' => 'Хіти продажів',
            'faq_items' => json_encode([
                ['question' => 'Чому варто обирати прикраси LAMARI?', 'answer' => 'Усі прикраси створені за авторською ідеєю та виконані з матеріалів найвищої якості. Виготовляємо прикраси на замовлення протягом 1–2 днів і відправляємо у брендованих коробочках.'],
                ['question' => 'Який матеріал фурнітури?', 'answer' => 'Ми обираємо матеріали класу люкс із якісним, стійким та гіпоалергенним покриттям. У золотому кольорі це позолота 18 карат по латуні, у срібному — латунь із покриттям родій або ювелірна сталь.'],
                ['question' => 'Які перли та каміння використовуєте?', 'answer' => 'Ми використовуємо тільки натуральні прісноводні перли та натуральне каміння.'],
                ['question' => 'У мене чутливі вуха. Який матеріал сережок?', 'answer' => 'Наші сережки мають гіпоалергенний сплав, не викликають алергії та дискомфорту.'],
                ['question' => 'Чи темніє прикраса?', 'answer' => 'Наші прикраси не темніють. Щоб прикраса якомога довше зберігала початковий вигляд, не рекомендуємо мочити її, особливо у солоній воді та басейні.'],
                ['question' => 'Можна мочити прикраси?', 'answer' => 'Прикраси з ювелірної сталі можна мочити. Прикраси з покриттям золотом і родієм рекомендуємо знімати перед душем, морем або басейном.'],
                ['question' => 'Який догляд за прикрасами?', 'answer' => 'Рекомендуємо знімати прикраси перед сном і душем, не наносити на них парфуми та креми безпосередньо, а також зберігати окремо.'],
                ['question' => 'Як довго мені прослужать прикраси?', 'answer' => 'Довговічність первинного вигляду залежить від pH шкіри, частоти носіння, контакту з парфумами та косметикою.'],
                ['question' => 'Чи можна обрати довжину прикраси?', 'answer' => 'Так, бажану довжину можна обрати у картці товару. Якщо потрібної довжини немає, зв’яжіться з нами через чат.'],
            ], JSON_UNESCAPED_UNICODE),
            'instagram_title' => 'Ви і Lamari Jewelry',
            'instagram_text' => 'Діліться своїми образами, відзначайте нас у Instagram, і ми із задоволенням додамо ваші фото',
            'instagram_url' => 'https://www.instagram.com/lamari.jewelry/',
            'instagram_images' => json_encode(array_map(fn ($n) => "/images/home/instagram/insta{$n}.png", range(1, 6))),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('show_on_home'));
        Schema::dropIfExists('homepage_settings');
    }
};
