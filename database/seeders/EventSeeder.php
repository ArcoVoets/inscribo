<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\MailerSettings;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $mailerSettings = MailerSettings::factory()
            ->count(3)
            ->create();

        Event::factory()
            ->count(5)
            ->recycle($mailerSettings)
            ->create();
    }
}
