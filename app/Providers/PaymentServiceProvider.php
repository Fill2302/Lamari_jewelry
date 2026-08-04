<?php

namespace App\Providers;

use App\Contracts\Payments\PaymentProvider;
use App\Payments\FakePaymentProvider;
use App\Payments\MonoPaymentProvider;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentProvider::class, fn () => match (config('services.payments.default')) {
            'fake' => new FakePaymentProvider,
            'mono' => new MonoPaymentProvider,
            default => throw new \RuntimeException('Unsupported payment provider'),
        });
    }
}
