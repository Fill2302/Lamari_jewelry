<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_management_pages_render(): void
    {
        $user = User::factory()->create();

        foreach ([
            '/admin',
            '/admin/products',
            '/admin/orders',
            '/admin/categories',
            '/admin/categories/create',
            '/admin/attributes',
            '/admin/attributes/create',
            '/admin/site-settings',
            '/admin/site-settings/create',
            '/admin/content-pages',
            '/admin/content-pages/create',
            '/admin/promo-codes',
            '/admin/promo-codes/create',
            '/admin/discounts',
            '/admin/discounts/create',
            '/admin/users',
            '/admin/users/create',
        ] as $path) {
            $this->actingAs($user)->get($path)->assertOk();
        }
    }
}
