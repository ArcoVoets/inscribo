<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Tables\InvitesTable;
use App\Models\Event;
use App\Models\Invite;
use App\Notifications\InvitedToRegisterNotification;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ManageEventInvites extends ManageRelatedRecords
{
    protected static string $resource = EventResource::class;

    protected static string $relationship = 'invites';

    public static function getNavigationIcon(): Heroicon
    {
        return Heroicon::OutlinedUserPlus;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.events.pages.manage_invites.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin.events.pages.manage_invites.title');
    }

    protected function getHeaderActions(): array
    {
        /** @var Event $event */
        $event = $this->getOwnerRecord();

        $capacityInfo = $event->inviteCapacityInfo();
        $hasCapacity = $capacityInfo['has_capacity'];

        $icon = $hasCapacity ? Heroicon::OutlinedCheck : Heroicon::ExclamationTriangle;
        $color = $hasCapacity ? 'success' : 'danger';

        return [
            Action::make('generateInvite')
                ->label(__('admin.events.pages.manage_invites.generate_invite'))
                ->modalWidth(Width::Large)
                ->modalIcon($icon)
                ->modalIconColor($color)
                ->modalAlignment(Alignment::Center)
                ->schema(fn (): array => [
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
                    TextInput::make('name')
                        ->label(__('admin.events.pages.manage_invites.name'))
                        ->helperText(__('admin.events.pages.manage_invites.name_helper'))
                        ->required(),
                    TextInput::make('email')
                        ->label(__('admin.events.pages.manage_invites.email'))
                        ->helperText(__('admin.events.pages.manage_invites.email_helper'))
                        ->email()
                        ->required(),
                    Select::make('invite_window_hours')
                        ->label(__('admin.states.waitlisted.invite_expires_after'))
                        ->options([
                            0 => __('admin.events.form.options.registration_expiration_never'),
                            1 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 1]),
                            6 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 6]),
                            12 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 12]),
                            24 => trans_choice('admin.events.form.options.registration_expiration_days', 1, ['days' => 1]),
                            2 * 24 => trans_choice('admin.events.form.options.registration_expiration_days', 2, ['days' => 2]),
                            7 * 24 => trans_choice('admin.events.form.options.registration_expiration_days', 7, ['days' => 7]),
                        ])
                        ->default(24)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $expiresAt = $data['invite_window_hours'] ? now()->addHours($data['invite_window_hours']) : null;

                    $invite = $this->getOwnerRecord()->invites()->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'token' => Invite::generateToken(),
                        'expires_at' => $expiresAt,
                    ]);

                    $success = true;
                    try {
                        $invite->notify(new InvitedToRegisterNotification($invite));
                    } catch (Exception $e) {
                        $success = false;
                        report($e);
                    }

                    if (! $success) {
                        Notification::make()
                            ->title(__('admin.events.pages.manage_invites.invite_created_but_email_failed'))
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title(__('admin.events.pages.manage_invites.invite_created'))
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return InvitesTable::configure($table);
    }
}
