<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MarketingAttributionTest extends TestCase
{
    public function test_it_captures_google_ads_attribution_and_keeps_first_touch(): void
    {
        Route::middleware('web')->get('/tracking-test', fn () => response('ok'));

        $first = $this->get('https://localhost/tracking-test?utm_source=google&utm_medium=cpc&utm_campaign=sale&gclid=click-1');
        $first->assertOk()->assertCookie('lamari_first_touch')->assertCookie('lamari_last_touch');

        $second = $this->get('https://localhost/tracking-test?utm_source=instagram&utm_campaign=remarketing&fbclid=meta-1');
        $second->assertOk()
            ->assertCookie('lamari_last_touch')
            ->assertSessionHas('marketing_attribution.first_touch.utm_source', 'google')
            ->assertSessionHas('marketing_attribution.first_touch.gclid', 'click-1')
            ->assertSessionHas('marketing_attribution.last_touch.utm_source', 'instagram')
            ->assertSessionHas('marketing_attribution.last_touch.fbclid', 'meta-1');
    }
}
