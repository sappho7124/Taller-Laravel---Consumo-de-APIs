<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }


    public function showCheckout()
    {
        return view('checkout');
    }


    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $paymentIntent = $this->stripeService->createPaymentIntent(
                (float) $validated['amount'],
                $validated['currency'],
                $validated['description'] ?? null
            );

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $sigHeader);
        } catch (\Exception $e) {
            Log::error("Webhook error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            Payment::updateOrCreate(
                ['stripe_payment_id' => $paymentIntent->id],
                [
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => strtoupper($paymentIntent->currency),
                    'status' => $paymentIntent->status,
                    'customer_email' => $paymentIntent->receipt_email ?? ($paymentIntent->charges->data[0]->billing_details->email ?? null),
                ]
            );

            Log::info("Pago registrado en BD: {$paymentIntent->id}");
        }

        return response()->json(['status' => 'success'], 200);
    }
}