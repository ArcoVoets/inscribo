<?php

namespace App\Actions;

use App\Enums\RegistrationStates;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Notifications\RegistrationCompletedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Laravel\Facades\Mollie;

class SyncMolliePaymentStatus
{
    public function execute(RegistrationPayment $registrationPayment): Payment
    {
        /** @var Payment $payment */
        $payment = Mollie::api()->payments->get($registrationPayment->mollie_payment_id);

        [$registration, $justPaid] = DB::transaction(function () use ($registrationPayment, $payment): array {
            $registrationPayment = RegistrationPayment::query()
                ->whereKey($registrationPayment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $registrationPayment->status = $payment->status;
            $registrationPayment->occured_at = match ($payment->status) {
                PaymentStatus::PAID => Carbon::parse($payment->paidAt),
                PaymentStatus::FAILED => Carbon::parse($payment->failedAt),
                PaymentStatus::CANCELED => Carbon::parse($payment->canceledAt),
                PaymentStatus::EXPIRED => Carbon::parse($payment->expiredAt),
                default => null,
            };
            $registrationPayment->save();

            if (! $payment->isPaid()) {
                return [null, false];
            }

            $registration = Registration::query()
                ->whereKey($registrationPayment->registration_id)
                ->lockForUpdate()
                ->firstOrFail();

            $alreadyRegistered = $registration->states()->where('type', RegistrationStates::Registered)->exists();
            if (! $alreadyRegistered) {
                $registration->states()->create([
                    'type' => RegistrationStates::Registered,
                ]);
            }

            return [$registration, ! $alreadyRegistered];
        });

        if ($justPaid && $registration !== null) {
            try {
                $email = $registration->notifyEmail();

                if ($email !== null) {
                    Notification::route('mail', $email)
                        ->notify(new RegistrationCompletedNotification($registration));
                }
            } catch (\Exception $e) {
                report($e);
            }
        }

        return $payment;
    }
}
