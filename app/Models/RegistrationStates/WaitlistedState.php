<?php

namespace App\Models\RegistrationStates;

use App\Enums\RegistrationStates;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationState;
use App\Notifications\InvitedFromWaitlistNotification;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Parental\HasParent;

class WaitlistedState extends RegistrationState
{
    use HasParent;

    public static string $filamentColor = 'gray';

    /**
     * @return array<int, Action>
     */
    public function filamentHeaderActions(): array
    {
        return [
            Action::make('invite')
                ->label(__('admin.states.waitlisted.invite'))
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->modalHeading(__('admin.states.waitlisted.invite_modal_heading'))
                ->modalWidth(Width::Large)
                ->schema(function (): array {
                    /** @var Event $event */
                    $event = $this->registration->event;
                    $capacityInfo = $event->inviteCapacityInfo();
                    $hasCapacity = $capacityInfo['has_capacity'];

                    $icon = $hasCapacity ? Heroicon::OutlinedCheck : Heroicon::ExclamationTriangle;
                    $color = $hasCapacity ? 'success' : 'danger';

                    return [
                        KeyValueEntry::make('capacity_info')
                            ->label(__('admin.capacity.current_status'))
                            ->state([
                                __('admin.capacity.total_capacity') => $capacityInfo['total_capacity'],
                                __('admin.capacity.registrations') => $capacityInfo['registrations_count'],
                                __('admin.capacity.pending_payments') => $capacityInfo['pending_payments_count'],
                                __('admin.capacity.pending_invites') => $capacityInfo['pending_invites_count'],
                            ]),
                        TextEntry::make('conclusion')
                            ->icon($icon)
                            ->label(__('admin.capacity.available_capacity'))
                            ->color($color)
                            ->badge()
                            ->state($capacityInfo['available_capacity']),
                        Select::make('invite_window_hours')
                            ->label(__('admin.states.waitlisted.invite_expires_after'))
                            ->options([
                                1 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 1]),
                                6 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 6]),
                                12 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 12]),
                                24 => trans_choice('admin.events.form.options.registration_expiration_days', 1, ['days' => 1]),
                                2 * 24 => trans_choice('admin.events.form.options.registration_expiration_days', 2, ['days' => 2]),
                                7 * 24 => trans_choice('admin.events.form.options.registration_expiration_days', 7, ['days' => 7]),
                            ])
                            ->default(24)
                            ->required(),
                    ];
                })
                ->action(function (array $data, Registration $record): void {
                    throw_if(
                        ! $record->currentState instanceof self,
                        new Exception('Registration is not in waitlisted state')
                    );

                    $inviteWindowHours = (int) $data['invite_window_hours'];
                    $expiresAt = now()->addHours($inviteWindowHours);

                    $record->states()->create([
                        'type' => RegistrationStates::PaymentPending,
                        'expires_at' => $expiresAt,
                    ]);

                    $notifyEmail = $record->notifyEmail();

                    if ($notifyEmail === null || $notifyEmail === '') {
                        Notification::make()
                            ->title(__('admin.states.waitlisted.payment_window_opened_no_email'))
                            ->body(__('admin.states.waitlisted.payment_window_opened_no_email_body'))
                            ->warning()
                            ->send();

                        return;
                    }

                    FacadesNotification::route('mail', $notifyEmail)
                        ->notify(new InvitedFromWaitlistNotification($record, $expiresAt));

                    Notification::make()
                        ->title(__('admin.states.waitlisted.payment_window_opened'))
                        ->success()
                        ->send();
                }),

            self::cancelAction(),
        ];
    }
}
