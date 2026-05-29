<?php

namespace App\Listeners;

use App\Notifications\BaseNotification;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Config;

class SetNotificationMailer
{
    /**
     * Handle the event.
     *
     * This listener runs when a notification is about to be sent, which allows us to
     * dynamically configure event-specific SMTP credentials.
     **/
    public function handle(NotificationSending $event): void
    {
        if (! $event->notification instanceof BaseNotification) {
            return;
        }

        $eventModel = $event->notification->getEvent();
        $mailerSettings = $eventModel->mailerSettings;

        if (! $mailerSettings) {
            return;
        }

        $mailerName = $eventModel->mailerName();

        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $mailerSettings->host,
            'port' => $mailerSettings->port,
            'username' => $mailerSettings->username,
            'password' => $mailerSettings->password,
        ]);
    }
}
