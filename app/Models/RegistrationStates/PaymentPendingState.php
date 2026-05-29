<?php

namespace App\Models\RegistrationStates;

use App\Models\Registration;
use App\Models\RegistrationState;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Parental\HasParent;

/** @property ?CarbonImmutable $expires_at */
class PaymentPendingState extends RegistrationState
{
    use HasParent;

    public static string $filamentColor = 'warning';

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
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** @return array<Component> */
    public function stateFields(): array
    {
        return [
            TextEntry::make('payment_link')
                ->label(__('admin.states.payment_pending.payment_link'))
                ->state(fn (Registration $record): string => $record->checkoutUrl($this->expires_at))
                ->hidden($this->isExpired())
                ->hint(__('admin.states.payment_pending.payment_link_hint'))
                ->copyable()
                ->columnSpanFull(),
        ];
    }
}
