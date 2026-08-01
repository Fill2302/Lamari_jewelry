<?php

namespace App\Models;

use App\Services\DiscountService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $guarded = [];
    protected $appends = ['effective_price_amount', 'original_price_amount', 'discount_percentage'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->stock_on_hand - $this->stock_reserved;
    }

    public function getEffectivePriceAmountAttribute(): int
    {
        return app(DiscountService::class)->priceFor($this);
    }

    public function getOriginalPriceAmountAttribute(): int
    {
        return (int) $this->getRawOriginal('price_amount');
    }

    public function getDiscountPercentageAttribute(): int
    {
        return app(DiscountService::class)->percentageFor($this);
    }
}
