<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentCallbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FakePaymentController extends Controller
{
    public function show(Payment $payment): InertiaResponse
    {
        return Inertia::render('FakePayment', ['payment' => $payment->load('order')]);
    }

    public function pay(Payment $payment, PaymentCallbackService $service): RedirectResponse
    {
        $raw = json_encode(['event_id' => (string) Str::uuid(), 'payment_id' => $payment->provider_payment_id, 'status' => 'paid']);
        $signature = hash_hmac('sha256', $raw, (string) config('services.payments.fake_secret'));
        $service->handle($raw, $signature);

        return redirect()->route('orders.thank-you', $payment->order)->with('success', 'Тестову оплату успішно проведено.');
    }

    public function callback(Request $r, PaymentCallbackService $service): Response
    {
        $processed = $service->handle($r->getContent(), $r->header('X-Fake-Signature'));

        return response($processed ? 'processed' : 'duplicate');
    }
}
