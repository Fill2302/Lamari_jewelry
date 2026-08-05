<?php

namespace App\Services;

use App\Models\PromoCode;

class PromoCodeService
{
    public function findValid(string $code, int $subtotal, bool $lock = false): ?PromoCode
    {
        $query = PromoCode::query()->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))]);
        if ($lock) {
            $query->lockForUpdate();
        }

        $promo = $query->first();
        if (! $promo || ! $promo->is_active
            || ($promo->starts_at && $promo->starts_at->isFuture())
            || ($promo->ends_at && $promo->ends_at->isPast())
            || ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit)
            || ($promo->minimum_order_amount !== null && $subtotal < $promo->minimum_order_amount)) {
            return null;
        }

        return $promo;
    }

    public function discount(PromoCode $promo, int $subtotal): int
    {
        $discount = $promo->discount_type === 'fixed'
            ? (int) $promo->discount_value
            : (int) round($subtotal * min((int) $promo->discount_value, 100) / 100);

        return min($discount, $subtotal);
    }
}
