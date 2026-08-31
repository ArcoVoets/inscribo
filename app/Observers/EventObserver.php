<?php

namespace App\Observers;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\EventMailTemplate;

class EventObserver
{
    public function creating(Event $event): void
    {
        $event->fill([
            'capacity_full_title' => $event->capacity_full_title ?: __('admin.events.form.default_messages.capacity_full_title'),
            'capacity_full_description' => $event->capacity_full_description ?: __('admin.events.form.default_messages.capacity_full_description'),
            'waitlist_active_title' => $event->waitlist_active_title ?: __('admin.events.form.default_messages.waitlist_active_title'),
            'waitlist_active_description' => $event->waitlist_active_description ?: __('admin.events.form.default_messages.waitlist_active_description'),
        ]);
    }

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
