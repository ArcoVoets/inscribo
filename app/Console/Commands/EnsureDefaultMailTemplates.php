<?php

namespace App\Console\Commands;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\EventMailTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class EnsureDefaultMailTemplates extends Command
{
    protected $signature = 'events:ensure-default-templates';

    protected $description = 'Ensure all events have the default mail templates (copy-on-create fallback for existing events)';

    public function handle(): int
    {
        Event::chunk(100, function (Collection $events): void {
            foreach ($events as $event) {
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
        });

        $this->info('Ensured default mail templates for all events.');

        return Command::SUCCESS;
    }
}
