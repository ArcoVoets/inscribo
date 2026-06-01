<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Event;
use App\Models\MailerSettings;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::factory()
            ->count(5)
            ->create();

        ApiKey::factory()
            ->count(3)
            ->create()
            ->each(function (ApiKey $apiKey) use ($events) {
                $events->random(rand(1, 3))->each->update(['api_key_id' => $apiKey->id]);
            });

        MailerSettings::factory()
            ->count(3)
            ->create()
            ->each(function (MailerSettings $mailerSettings) use ($events) {
                $events->random(rand(1, 3))->each->update(['mailer_settings_id' => $mailerSettings->id]);
            });
    }
}
