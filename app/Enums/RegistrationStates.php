<?php

namespace App\Enums;

use App\Models\RegistrationState;
use App\Models\RegistrationStates\CancelledState;
use App\Models\RegistrationStates\PaymentExpiredState;
use App\Models\RegistrationStates\PaymentPendingState;
use App\Models\RegistrationStates\RefundedState;
use App\Models\RegistrationStates\RegisteredState;
use App\Models\RegistrationStates\WaitlistedState;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RegistrationStates: string implements HasColor, HasLabel
{
    case Waitlisted = 'waitlisted';
    case PaymentPending = 'payment_pending';
    case Registered = 'registered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    // Note: This state is not intended to be stored in the database but is used in the
    // application for registrations in the PaymentPending state for which the payment has expired.
    // This is done to simplify the handling of expired payments, without having to check the
    // expires_at field of PaymentPendingState in multiple places in the codebase.
    case PaymentExpired = 'payment_expired';

    public function getColor(): string
    {
        return $this->getModelClass()::$filamentColor;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Waitlisted => __('registration.states.waitlisted'),
            self::PaymentPending => __('registration.states.payment_pending'),
            self::Registered => __('registration.states.registered'),
            self::Cancelled => __('registration.states.cancelled'),
            self::Refunded => __('registration.states.refunded'),
            self::PaymentExpired => __('registration.states.payment_expired'),
        };
    }

    /** @return class-string<RegistrationState> */
    public function getModelClass(): string
    {
        return match ($this) {
            RegistrationStates::Waitlisted => WaitlistedState::class,
            RegistrationStates::PaymentPending => PaymentPendingState::class,
            RegistrationStates::Registered => RegisteredState::class,
            RegistrationStates::Cancelled => CancelledState::class,
            RegistrationStates::Refunded => RefundedState::class,
            RegistrationStates::PaymentExpired => PaymentExpiredState::class,
        };
    }

    /** @return array<self> */
    public static function reservedStates(): array
    {
        return [
            self::PaymentPending,
            self::Registered,
        ];
    }

    public static function tabsOrder(): array
    {
        return [
            self::Registered,
            self::PaymentPending,
            self::Waitlisted,
            self::Cancelled,
            self::PaymentExpired,
            self::Refunded,
        ];
    }
}
