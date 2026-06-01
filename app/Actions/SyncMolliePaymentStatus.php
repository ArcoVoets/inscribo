<?php

namespace App\Actions;

use App\Enums\RegistrationStates;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Notifications\RegistrationCompletedNotification;
use App\Services\MollieService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Types\PaymentStatus;

class SyncMolliePaymentStatus
{
    public function __construct(
        private readonly MollieService $mollieService,
    ) {}

    public function execute(RegistrationPayment $registrationPayment): Payment
    {
        /** @var Payment $payment */
        $payment = $this->mollieService->getPayment($registrationPayment->registration->event, $registrationPayment->mollie_payment_id);

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
                $registration->notify(new RegistrationCompletedNotification($registration));
            } catch (\Exception $e) {
                report($e);
            }
        }

        return $payment;
    }
}
