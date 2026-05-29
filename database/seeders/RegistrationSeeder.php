<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EventSeeder::class);

        $events = Event::query()->get();

        foreach ($events as $event) {
            // Default factory creates a waitlisted state.
            Registration::factory()->count(25)->for($event)->create();

            Registration::factory()->count(20)->for($event)->paymentPending()->create();
            Registration::factory()->count(15)->for($event)->registered()->create();
            Registration::factory()->count(5)->for($event)->cancelled()->create();
            Registration::factory()->count(5)->for($event)->refunded()->create();
        }
    }
}
