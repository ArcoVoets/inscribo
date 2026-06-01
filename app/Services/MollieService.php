<?php

namespace App\Services;

use App\Models\Event;
use Mollie\Api\Resources\Payment;
use Mollie\Laravel\Facades\Mollie;

class MollieService
{
    private function setApiKeyForEvent(Event $event): void
    {
        if ($event->apiKey !== null) {
            config()->set('mollie.key', $event->apiKey->key);
        }
    }

    public function getPayment(Event $event, string $molliePaymentId): Payment
    {
        $this->setApiKeyForEvent($event);

        return Mollie::api()->payments->get($molliePaymentId);
    }

    public function createPayment(Event $event, array $paymentData): Payment
    {
        $this->setApiKeyForEvent($event);

        return Mollie::api()->payments->create($paymentData);
    }
}
