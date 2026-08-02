<?php

namespace App\Services;

use Illuminate\Http\Request;

class MarketingAttribution
{
    public function from(Request $request): ?array
    {
        $session = $request->session()->get('marketing_attribution');
        if (is_array($session)) {
            return $session;
        }

        $first = $this->decode($request->cookie('lamari_first_touch'));
        $last = $this->decode($request->cookie('lamari_last_touch'));
        if ($first === null && $last === null) {
            return null;
        }

        return [
            'first_touch' => $first ?? $last,
            'last_touch' => $last ?? $first,
        ];
    }

    private function decode(mixed $value): ?array
    {
        $decoded = is_string($value) ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : null;
    }
}
