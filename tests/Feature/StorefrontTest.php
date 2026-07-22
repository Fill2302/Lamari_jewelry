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
        $this->get('/categories/rings')->assertOk();
        $this->get('/products/aurelia-ring')->assertOk();
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /*?*');
    }
}
