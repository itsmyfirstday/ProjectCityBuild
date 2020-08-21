<?php

namespace App\Library\Stripe;

class StripeWebhook
{
    protected $event;
    protected $transactionId;

    public function __construct(StripeWebhookEvent $event, string $transactionId)
    {
        $this->event = $event;
        $this->transactionId = $transactionId;
    }

    public function getEvent(): StripeWebhookEvent
    {
        return $this->event;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }
}