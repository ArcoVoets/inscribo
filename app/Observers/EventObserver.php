<?php

namespace App\Observers;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\EventMailTemplate;

class EventObserver
{
    public function created(Event $event): void
    {
        if (! $event->form) {
            $event->form()->create([
                'title' => $event->title,
                'base_price_cents' => 0,
            ]);
        }

        // Create default mail templates for the event (copy-on-create)
        foreach (EventMailTemplateType::cases() as $type) {
            EventMailTemplate::firstOrCreate([
                'event_id' => $event->id,
                'type' => $type,
            ], [
                'subject' => $type->defaultSubject($event),
                'content' => $type->defaultContent($event),
            ]);
        }
    }
}
