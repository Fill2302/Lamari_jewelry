<?php

namespace App\Services;

use App\Models\MerchantAccount;
use RuntimeException;

class MerchantSelector
{
    public function select(int $total, ?string $paymentDestination = null): MerchantAccount
    {
        $items = MerchantAccount::with('legalEntity')->where('provider', config('services.payments.default'))->where('is_active', true)->get();
        $destinationItems = in_array($paymentDestination, ['mono', 'privat'], true)
            ? $items->where('payment_destination', $paymentDestination)
            : collect();
        $candidates = $destinationItems->isNotEmpty()
            ? $destinationItems
            : ($paymentDestination === 'unassigned' ? $items : $items->whereNull('payment_destination'));
        $match = $candidates->first(fn ($m) => $this->matches($m->selection_rules ?? [], $total))
            ?? $candidates->firstWhere('is_default', true)
            ?? $candidates->first();

        return $match ?? throw new RuntimeException('No active payment merchant is configured.');
    }

    private function matches(array $r, int $a): bool
    {
        return isset($r['min_amount']) && $a >= $r['min_amount'] && (! isset($r['max_amount']) || $a <= $r['max_amount']);
    }
}
