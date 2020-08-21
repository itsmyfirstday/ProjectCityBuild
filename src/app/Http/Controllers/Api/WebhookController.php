<?php

namespace App\Http\Controllers\Api;

use App\Http\ApiController;
use App\Library\Stripe\StripeHandler;
use App\Library\Stripe\StripePaymentWebhook;
use App\Library\Stripe\StripeWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class WebhookController extends ApiController
{
    public function stripe(Request $request, StripeHandler $stripeHandler)
    {
        $endpointSecret = config('services.stripe.webhook.secret');
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');

        Log::debug('Received Stripe webhook', [
            'payload' => $payload,
            'signature' => $signature,
        ]);

        $webhookJSON = $stripeHandler->getWebhookEvent($payload, $signature, $endpointSecret);

        try {
            $webhookEvent = StripeWebhookEvent::fromRawValue($webhookJSON->type);
        } catch(\Exception $e) {
            Log::info('Unhandled Stripe webhook event');
            return null;
        }

        switch ($webhookEvent) {
            case StripeWebhookEvent::CheckoutSessionCompleted:
                Log::info('Webhook acknowledged');

                $paymentWebhook = StripePaymentWebhook::fromJSON($webhookJSON, $webhookEvent);

                $controller = new DonationController($stripeHandler);
                $response = $controller->store($paymentWebhook);
                return $response;

            default:
                Log::info('Webhook ignored');
                return response()->json(null, 204);
        }
    }
}