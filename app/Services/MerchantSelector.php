<?php

namespace App\Services;

use App\Models\MerchantAccount;
use RuntimeException;

class MerchantSelector
{
    public function select(int $total): MerchantAccount
    {
        $items = MerchantAccount::with('legalEntity')->where('provider', config('services.payments.default'))->where('is_active', true)->get();
        $match = $items->first(fn ($m) => $this->matches($m->selection_rules ?? [], $total)) ?? $items->firstWhere('is_default', true);

        return $match ?? throw new RuntimeException('No active payment merchant is configured.');
    }

    private function matches(array $r, int $a): bool
    {
        return isset($r['min_amount']) && $a >= $r['min_amount'] && (! isset($r['max_amount']) || $a <= $r['max_amount']);
    }
}
