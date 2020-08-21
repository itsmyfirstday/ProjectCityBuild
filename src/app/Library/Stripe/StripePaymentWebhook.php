<?php

namespace App\Library\Stripe;

final class StripePaymentWebhook extends StripeWebhook
{
    private $sessionId;
    private $customerId;
    private $amountInCents;
    private $subscriptionId;

    public function __construct(StripeWebhookEvent $event, string $transactionId, string $sessionId, string $customerId, int $amountInCents, ?string $subscriptionId)
    {
        parent::__construct($event, $transactionId);

        $this->sessionId = $sessionId;
        $this->customerId = $customerId;
        $this->amountInCents = $amountInCents;
        $this->subscriptionId = $subscriptionId;
    }

    public static function fromJSON($json, StripeWebhookEvent $event): ?StripeWebhook
    {
        $object = $json->data->object;
        $displayItem = $object->display_items[0];

        $subscriptionId = null;
        if ($object->subscription) {
            $subscriptionId = $object->subscription;
        }

        return new StripePaymentWebhook(
            $event,
            $object->id,
            $object->client_reference_id,
            $object->customer,
            $displayItem->amount * $displayItem->quantity,
            $subscriptionId
        );
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function subscriptionId(): string
    {
        return $this->subscriptionId;
    }
}