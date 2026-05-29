<?php

namespace App\Enums;

use App\Models\Event;
use App\Notifications\BaseNotification;
use App\Notifications\InvitedFromWaitlistNotification;
use App\Notifications\InvitedToRegisterNotification;
use App\Notifications\RegistrationCompletedNotification;
use App\Notifications\RegistrationSubmittedPaymentPending;
use App\Notifications\WaitlistedNotification;
use Filament\Support\Contracts\HasLabel;

enum EventMailTemplateType: string implements HasLabel
{
    case RegistrationSubmittedPaymentPending = 'registration_submitted_payment_pending';
    case Waitlisted = 'waitlisted';
    case InvitedFromWaitlist = 'invited_from_waitlist';
    case RegistrationCompleted = 'registration_completed';
    case InvitedToRegister = 'invited_to_register';

    public function getLabel(): string
    {
        return match ($this) {
            self::RegistrationSubmittedPaymentPending => __('admin.events.mail_templates.types.registration_submitted_payment_pending'),
            self::Waitlisted => __('admin.events.mail_templates.types.waitlisted'),
            self::InvitedFromWaitlist => __('admin.events.mail_templates.types.invited_from_waitlist'),
            self::RegistrationCompleted => __('admin.events.mail_templates.types.registration_completed'),
            self::InvitedToRegister => __('admin.events.mail_templates.types.invited_to_register'),
        };
    }

    /** @return array<string, string> */
    public function allMergeTagLabels(): array
    {
        return $this->notificationClass()::allMergeTagLabels();
    }

    /** @return class-string<BaseNotification> */
    public function notificationClass(): string
    {
        return match ($this) {
            self::RegistrationSubmittedPaymentPending => RegistrationSubmittedPaymentPending::class,
            self::Waitlisted => WaitlistedNotification::class,
            self::InvitedFromWaitlist => InvitedFromWaitlistNotification::class,
            self::RegistrationCompleted => RegistrationCompletedNotification::class,
            self::InvitedToRegister => InvitedToRegisterNotification::class,
        };
    }

    public function defaultSubject(Event $event): string
    {
        return $this->notificationClass()::defaultTemplateSubject($event);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaultContent(Event $event): array
    {
        return $this->notificationClass()::defaultTemplateContent($event);
    }
}
