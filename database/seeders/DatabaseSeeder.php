<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\LegalEntity;
use App\Models\MerchantAccount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create(['name' => 'Lamari Admin', 'email' => 'admin@lamari.test', 'password' => 'password']);
        $entity = LegalEntity::create(['name' => 'Lamari Test ФОП', 'tax_id' => 'TEST000000']);
        MerchantAccount::create(['legal_entity_id' => $entity->id, 'provider' => 'fake', 'code' => 'fake-primary', 'is_default' => true, 'test_mode' => true]);
        $tree = [
            'sale' => ['Sale', []],
            'summer' => ['Літня колекція', ['summer-chokers' => 'Чокери', 'summer-necklaces' => 'Кольє', 'summer-chains' => 'Ланцюжки', 'summer-bracelets' => 'Браслети', 'summer-earrings' => 'Сережки', 'summer-sets' => 'Комплекти', 'summer-pins' => 'Булавки']],
            'necklaces' => ['Кольє', ['necklaces-crystal' => 'З кришталю', 'necklaces-pearls' => 'З перлами', 'necklaces-chain' => 'З ланцюжка', 'necklaces-stones' => 'З натуральним камінням']],
            'chokers' => ['Чокери', ['chokers-crystal' => 'З кришталю', 'chokers-pearls' => 'З перлами', 'chokers-stones' => 'З натуральним камінням', 'chokers-cords' => 'Шнури', 'chokers-beads' => 'З бісеру']],
            'earrings' => ['Сережки', ['earrings-zirconia' => 'З фіанітами', 'earrings-pearls' => 'З перлами', 'earrings-stones' => 'З натуральним камінням', 'earrings-basic' => 'Базові сережки']],
            'chains' => ['Ланцюжки', ['chains-basic' => 'Базові ланцюжки', 'chains-pearls' => 'З перлинами', 'chains-stones' => 'З натуральним камінням']],
            'bracelets' => ['Браслети', ['bracelets-pearls' => 'З перлами', 'bracelets-stones' => 'З натуральним камінням', 'bracelets-beads' => 'З бісеру', 'bracelets-chain' => 'З ланцюжка', 'bracelets-crystal' => 'З кришталю']],
            'anklets' => ['Анклети', ['anklets-pearls' => 'З перлами', 'anklets-stones' => 'З натуральним камінням', 'anklets-beads' => 'З бісеру', 'anklets-chain' => 'З ланцюжка']],
            'pins' => ['Булавки', []],
            'rings' => ['Каблучки', ['rings-accent' => 'Акцентні', 'rings-pearls' => 'З перлами', 'rings-stones' => 'З натуральним камінням', 'rings-chain' => 'З ланцюжка']],
            'sets' => ['Комплекти', ['sets-crystal' => 'З кришталю', 'sets-pearls' => 'З перлами', 'sets-stones' => 'З натуральним камінням', 'sets-chain' => 'З ланцюжка']],
        ];
        $categories = [];
        foreach ($tree as $slug => [$name, $children]) {
            $parent = Category::create(['name' => $name, 'slug' => $slug, 'description' => "Авторські прикраси Lamari — {$name}.", 'seo_title' => "{$name} — Lamari Jewelry"]);
            $categories[$slug] = $parent;
            foreach ($children as $childSlug => $childName) {
                $categories[$childSlug] = Category::create(['parent_id' => $parent->id, 'name' => $childName, 'slug' => $childSlug, 'description' => "{$name}: {$childName}."]);
            }
        }

        $product = Product::create([
            'category_id' => $categories['necklaces-crystal']->id,
            'name' => 'Кольє з кришталю та перлин',
            'slug' => 'crystal-pearl-necklace',
            'description' => 'Ніжне кольє у поєднанні натуральних перлин та кришталю. Стандартна довжина — 43 см.',
            'material' => 'Кришталь, натуральні перлини',
            'characteristics' => ['Матеріал фурнітури' => 'Латунь', 'Покриття' => 'Позолота 18к', 'Тип застібки' => 'Тогл з фіанітами'],
            'packaging_text' => 'Безкоштовно пакуємо у подарункову брендовану коробочку — для отримання, дарування та зберігання прикрас.',
            'care_text' => 'Прикраси з латуні рекомендуємо знімати перед душем, морем або басейном та зберігати в сухому місці.',
            'image_url' => 'products/crystal-pearl-necklace-1.webp',
            'seo_title' => 'Кольє з кришталю та перлин — Lamari Jewelry',
            'seo_description' => 'Ніжне авторське кольє Lamari з натуральних перлин та прозорого кришталю.',
            'published_at' => now(),
        ]);
        foreach (['43 см', '45 см', '50 см', '55 см'] as $length) {
            $product->variants()->create(['sku' => 'K423-'.str_replace(' см', '', $length), 'name' => $length, 'price_amount' => 199000, 'stock_on_hand' => 8]);
        }
        foreach ([
            ['image', 'products/crystal-pearl-necklace-1.webp', null, 'Кольє з кришталю та перлин на моделі'],
            ['image', 'products/crystal-pearl-necklace-2.webp', null, 'Деталі кольє з кришталю та перлин'],
            ['image', 'products/crystal-pearl-necklace-3.webp', null, 'Кольє з кришталю та перлин у комплекті з прикрасами'],
        ] as $position => [$type, $url, $poster, $alt]) {
            $product->media()->create(['type' => $type, 'url' => $url, 'poster_url' => $poster, 'alt' => $alt, 'position' => $position]);
        }
    }
}
