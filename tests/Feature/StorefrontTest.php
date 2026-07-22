<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_storefront_and_seo_endpoints_work(): void
    {
        $this->seed();
        $this->get('/')->assertOk()->assertSee('Home', false);
        $this->get('/categories/necklaces')->assertOk();
        $this->get('/products/crystal-pearl-necklace')->assertOk()->assertSee('video', false);
        $this->assertDatabaseHas('product_media', ['type' => 'video', 'is_active' => true]);
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /*?*');
    }
}
