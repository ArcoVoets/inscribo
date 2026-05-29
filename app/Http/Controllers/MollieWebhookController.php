<?php

namespace App\Http\Controllers;

use App\Actions\SyncMolliePaymentStatus;
use App\Models\RegistrationPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class MollieWebhookController extends Controller
{
    public function __invoke(Request $request, SyncMolliePaymentStatus $syncPaymentStatus): Response
    {
        $paymentId = $request->string('id');

        if ($paymentId->isEmpty()) {
            info('Mollie webhook received without payment id', ['payload' => $request->all()]);

            return response('OK', 200);
        }

        $registrationPayment = RegistrationPayment::query()
            ->where('mollie_payment_id', $paymentId)
            ->first();

        if (! $registrationPayment) {
            info('Mollie webhook: registration not found', [
                'payment_id' => $paymentId,
            ]);

            return response('OK', 200);
        }

        try {
            $syncPaymentStatus->execute($registrationPayment);
        } catch (Throwable $e) {
            report($e);

            return response('Failed to fetch payment', 500);
        }

        return response('OK', 200);
    }
}
