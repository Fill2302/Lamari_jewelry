<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Payments\WayForPayPaymentProvider;
use App\Services\PaymentCallbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WayForPayPaymentController extends Controller
{
    public function checkout(Payment $payment): View
    {
        abort_unless($payment->provider === 'wayforpay' && $payment->status === 'pending', 404);

        return view('payments.wayforpay', [
            'fields' => data_get($payment->payload, 'checkout', []),
        ]);
    }

    public function return(Payment $payment): RedirectResponse
    {
        abort_unless($payment->provider === 'wayforpay', 404);

        return redirect()->route('orders.thank-you', $payment->order);
    }

    public function webhook(Request $request, PaymentCallbackService $service, WayForPayPaymentProvider $provider): JsonResponse
    {
        $payload = $request->json()->all();
        $service->handleWith($provider, $request->getContent(), data_get($payload, 'merchantSignature'));

        return response()->json($provider->acceptance((string) data_get($payload, 'orderReference')));
    }
}
