<?php

namespace App\Models\RegistrationStates;

use App\Enums\RegistrationStates;
use App\Models\Registration;
use App\Models\RegistrationState;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Parental\HasParent;

class CancelledState extends RegistrationState
{
    use HasParent;

    public static string $filamentColor = 'danger';

    /**
     * @return array<int, Action>
     */
    public function filamentHeaderActions(): array
    {
        return [
            Action::make('markRefunded')
                ->label(__('admin.states.cancelled.mark_refunded'))
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('primary')
                ->visible(fn (Registration $record): bool => $record->states()->where('type', 'registered')->exists()
                    && (! $record->states()->where('type', 'refunded')->exists()))
                ->schema([
                    TextInput::make('amount_cents')
                        ->label(__('admin.states.cancelled.refund_amount'))
                        ->numeric()
                        ->prefix('€')
                        ->inputMode('decimal')
                        ->step('0.01')
                        ->minValue(0)
                        ->default(fn (Registration $record): ?int => $record->states()->where('type', 'registered')->latest('created_at')->first()?->amount_cents)
                        ->formatStateUsing(fn ($state): ?string => $state === null ? null : (int) $state / 100)
                        ->dehydrateStateUsing(function ($state): ?int {
                            if ($state === null || $state === '') {
                                return null;
                            }

                            return (int) round(((float) $state) * 100);
                        })
                        ->required(),
                ])
                ->action(function (array $data, Registration $record): void {
                    $amountCents = $data['amount_cents'];

                    $record->states()->create([
                        'type' => RegistrationStates::Refunded,
                        'amount_cents' => $amountCents,
                    ]);

                    Notification::make()
                        ->title(__('admin.states.cancelled.registration_refunded'))
                        ->success()
                        ->send();

                    $record->refresh();
                }),
        ];
    }
}
