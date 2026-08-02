<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingAttribution
{
    private const KEYS = [
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_id', 'utm_term', 'utm_content',
        'gclid', 'gbraid', 'wbraid', 'dclid', 'fbclid', 'msclkid',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $click = collect(self::KEYS)
            ->mapWithKeys(fn (string $key): array => [$key => $this->clean($request->query($key))])
            ->filter(fn (?string $value): bool => $value !== null)
            ->all();

        if ($click === []) {
            return $next($request);
        }

        $touch = [
            ...$click,
            'landing_page' => $request->fullUrl(),
            'referrer' => $this->clean($request->headers->get('referer')),
            'captured_at' => now()->toIso8601String(),
        ];
        $sessionFirst = $request->session()->get('marketing_attribution.first_touch');
        $existingFirst = is_array($sessionFirst)
            ? $sessionFirst
            : $this->decode($request->cookie('lamari_first_touch'));
        $first = $existingFirst ?? $touch;

        $request->session()->put('marketing_attribution', [
            'first_touch' => $first,
            'last_touch' => $touch,
        ]);

        $response = $next($request);
        $minutes = 60 * 24 * 90;

        if ($existingFirst === null) {
            $response->headers->setCookie(cookie('lamari_first_touch', json_encode($touch), $minutes, '/', null, true, true, false, 'Lax'));
        }
        $response->headers->setCookie(cookie('lamari_last_touch', json_encode($touch), $minutes, '/', null, true, true, false, 'Lax'));

        return $response;
    }

    private function clean(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return mb_substr(trim($value), 0, 2048);
    }

    private function decode(mixed $value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
