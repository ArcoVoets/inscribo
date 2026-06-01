<?php

namespace App\Contracts;

use Illuminate\Notifications\Notification;

interface Notifiable
{
    public function canBeSendToMail(): bool;

    public function routeNotificationForMail(Notification $notification): array|string;
}
