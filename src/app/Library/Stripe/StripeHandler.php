<?php

namespace App\Library\Stripe;

use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Webhook;

class StripeHandler
{
    private $currency = 'usd';

    public function __construct()
    {
        $this->setApiKey();
        $this->setCurrency($this->currency);
    }

    private function setApiKey()
    {
        $key = config('services.stripe.secret');
        if (empty($key)) {
            throw new \Exception('No Stripe API secret set');
        }
        Stripe::setApiKey($key);
    }

    public function setCurrency(string $currency) : StripeHandler
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Creates a new session for using Stripe's Checkout flow
     *
     * @param string $uniqueSessionId   A unique UUID that will be sent to Stripe to be stored alongside the
     *                                  session. When Stripe notifies us via WebHook that the payment is processed,
     *                                  they will pass us back the UUID so we can fulfill the purchase.
     * @param int $amountInCents
     * @param string $customerId        Associated Stripe customer id
     * @return string Stripe session ID
     */
    public function createOneTimeCheckoutSession(string $uniqueSessionId, int $amountInCents, ?string $customerId = null): string
    {
        $sessionData = [
            'payment_method_types' => ['card'],
            'client_reference_id' => $uniqueSessionId,
            'line_items' => [
                [
                    'name' => 'PCB Contribution',
                    'description' => 'One time payment',
                    'images' => [],
                    'amount' => $amountInCents,
                    'currency' => $this->currency,
                    'quantity' => 1,
                ],
            ],
            'success_url' => route('front.donate.success'),
            'cancel_url' => route('front.donate'),
        ];

        if ($customerId !== null) {
            $sessionData['customer'] = $customerId;
        }

        $session = Session::create($sessionData);

        return $session->id;
    }

    public function createRecurringCheckoutSession(string $uniqueSessionId, int $amountInCents, ?string $customerId = null): string
    {
        $sessionData = [
            'payment_method_types' => ['card'],
            'client_reference_id' => $uniqueSessionId,
            'subscription_data' => [
                'items' => [[
                    'plan' => config('services.stripe.plans.recurring'),
                    'quantity' => round($amountInCents / 100),
                ]],
            ],
            'success_url' => route('front.donate.success', ['session_id' => '{CHECKOUT_SESSION_ID}']),
            'cancel_url' => route('front.donate'),
        ];

        if ($customerId !== null) {
            $sessionData['customer'] = $customerId;
        }

        $session = Session::create($sessionData);

        return $session->id;
    }

    /**
     * Receives a Webhook from Stripe and parses it into a generalized model
     *
     * @param string $payload
     * @param string $signature
     * @param string $secret
     * @throws \Stripe\Error\SignatureVerification
     * @return Event
     */
    public function getWebhookEvent(string $payload, string $signature, string $secret): Event
    {
        return Webhook::constructEvent($payload, $signature, $secret);
    }
}