<?php

namespace App\Models\RegistrationStates;

use App\Enums\RegistrationStates;
use App\Models\RegistrationState;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Parental\HasParent;

/** @property CarbonImmutable $expires_at */
class PaymentExpiredState extends RegistrationState
{
    use HasParent;

    public static string $filamentColor = 'danger';

    public function classToAlias(string $className): mixed
    {
        if ($className === self::class) {
            return RegistrationStates::PaymentPending->value;
        }

        return parent::classToAlias($className);
    }

    public function getTypeAttribute(mixed $value): RegistrationStates
    {
        return RegistrationStates::PaymentExpired;
    }

    public function setTypeAttribute(mixed $value): void
    {
        $this->attributes['type'] = RegistrationStates::PaymentPending->value;
    }

    protected static function booted(): void
    {
        static::addGlobalScope('paymentExpired', function (Builder $query): void {
            $query
                ->where('type', RegistrationStates::PaymentPending)
                ->wherePast('expires_at');
        });
    }

    /**
     * @return array<int, Action>
     */
    public function filamentHeaderActions(): array
    {
        return [
            self::cancelAction(),
        ];
    }

    public function isExpired(): bool
    {
        throw_if(
            $this->expires_at === null || $this->expires_at->isFuture(),
            new LogicException('PaymentExpiredState should only be used for expired payments.')
        );

        return true;
    }
}
