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
        $rings = Category::create(['name' => 'Каблучки', 'slug' => 'rings', 'description' => 'Виразні каблучки для щоденних образів.', 'seo_title' => 'Каблучки Lamari', 'seo_description' => 'Сучасні каблучки Lamari.']);
        $earrings = Category::create(['name' => 'Сережки', 'slug' => 'earrings', 'description' => 'Лаконічні сережки з характером.']);
        foreach ([[$rings, 'Каблучка Aurelia', 'aurelia-ring', 'Позолочена каблучка з м’якою органічною формою.', 'Латунь, позолота', 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=1200', ['16' => 189000, '17' => 189000]], [$rings, 'Каблучка Selene', 'selene-ring', 'Скульптурна срібляста каблучка.', 'Ювелірна сталь', 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=1200', ['16' => 149000, '18' => 149000]], [$earrings, 'Сережки Lune', 'lune-earrings', 'Тонкі сережки-кільця теплого золотого відтінку.', 'Латунь, позолота', 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=1200', ['One size' => 219000]]] as [$cat,$name,$slug,$desc,$material,$image,$variants]) {
            $p = Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => $slug, 'description' => $desc, 'material' => $material, 'image_url' => $image, 'seo_title' => $name.' — Lamari', 'seo_description' => $desc, 'published_at' => now()]);
            foreach ($variants as $v => $price) {
                $p->variants()->create(['sku' => strtoupper(substr($slug, 0, 4)).'-'.str_replace(' ', '', $v), 'name' => $v, 'price_amount' => $price, 'stock_on_hand' => 8]);
            }
        }
    }
}
