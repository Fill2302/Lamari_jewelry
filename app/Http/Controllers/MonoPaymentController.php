<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentCallbackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonoPaymentController extends Controller
{
    public function return(Payment $payment): RedirectResponse
    {
        abort_unless($payment->provider === 'mono', 404);

        return redirect()->route('orders.thank-you', $payment->order);
    }

    public function webhook(Request $request, PaymentCallbackService $service): Response
    {
        $processed = $service->handle($request->getContent(), $request->header('X-Sign'));

        return response($processed ? 'processed' : 'ignored');
    }
}
