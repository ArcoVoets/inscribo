<?php

namespace App\Notifications;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use Override;

class WaitlistedNotification extends RegistrationNotification
{
    #[Override]
    public static function templateType(): EventMailTemplateType
    {
        return EventMailTemplateType::Waitlisted;
    }

    #[Override]
    public static function defaultTemplateSubject(Event $event): string
    {
        return __('mail.waitlisted.subject', [
            'event_title' => $event->title,
        ]);
    }

    #[Override]
    public static function defaultTemplateContent(Event $event): array
    {
        $json = __('mail-templates.waitlisted');

        return json_decode($json, true);
    }
}
