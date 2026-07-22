<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_add_update_and_remove_flow(): void
    {
        $this->seed();
        $variant = ProductVariant::firstOrFail();

        $this->post("/cart/{$variant->id}", ['quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas('cartOpen', true)
            ->assertSessionHas("cart.{$variant->id}.quantity", 1);

        $this->put("/cart/{$variant->id}", ['quantity' => 3])
            ->assertRedirect()
            ->assertSessionHas("cart.{$variant->id}.quantity", 3);

        $this->delete("/cart/{$variant->id}")
            ->assertRedirect()
            ->assertSessionMissing("cart.{$variant->id}");
    }

    public function test_cart_quantity_cannot_exceed_available_stock(): void
    {
        $this->seed();
        $variant = ProductVariant::firstOrFail();
        $variant->update(['stock_on_hand' => 2]);

        $this->post("/cart/{$variant->id}", ['quantity' => 10])
            ->assertSessionHas("cart.{$variant->id}.quantity", 2);
    }
}
