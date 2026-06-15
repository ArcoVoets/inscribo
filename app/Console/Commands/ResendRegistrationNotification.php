<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Registration;
use App\Notifications\InvitedFromWaitlistNotification;
use App\Notifications\RegistrationCompletedNotification;
use App\Notifications\RegistrationNotification;
use App\Notifications\RegistrationSubmittedPaymentPendingNotification;
use App\Notifications\WaitlistedNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;

#[Signature('app:resend-registration-notification')]
#[Description('Resend a registration notification, can be used when a mail was not delivered')]
class ResendRegistrationNotification extends Command
{
    public function handle()
    {
        $eventId = select('Select event', Event::all()->pluck('title', 'id')->toArray());

        /** @var Event $event */
        $event = Event::find($eventId);

        $registrations = $event->registrations->mapWithKeys(function ($registration) {
            return [$registration->id => $registration->name() ?? $registration->notifyEmail() ?? $registration->id];
        })->toArray();
        $registrationId = select('Select registration', $registrations);
        /** @var Registration $registration */
        $registration = Registration::find($registrationId);

        /** @var class-string<RegistrationNotification> */
        $notificationType = select('Select notification type', [
            InvitedFromWaitlistNotification::class => 'InvitedFromWaitlistNotification',
            RegistrationCompletedNotification::class => 'RegistrationConfirmedNotification',
            RegistrationSubmittedPaymentPendingNotification::class => 'RegistrationSubmittedPaymentPendingNotification',
            WaitlistedNotification::class => 'WaitlistedNotification',
        ]);

        $registration->notify(new $notificationType($registration));

        $this->info('Notification sent');
    }
}
