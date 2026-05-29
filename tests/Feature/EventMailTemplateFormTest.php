<?php

use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Models\Event;
use Filament\Schemas\Schema;

it('exposes the mail template repeater on the event form', function () {
    $page = app(EditEvent::class);
    $page->record = Event::withoutEvents(function (): Event {
        return Event::query()->create([
            'title' => 'Example event',
            'capacity' => 25,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDay(),
            'show_waitlist_position' => false,
            'show_capacity_data' => false,
            'registration_expiration_minutes' => 30,
        ]);
    });

    $schema = EventForm::configure(Schema::make($page)->model($page->record));

    $fields = array_keys($schema->getFlatFields(withHidden: true));

    expect($fields)->toContain('mailTemplates');
});

it('exposes registration form color fields on the event form', function () {
    $page = app(EditEvent::class);
    $page->record = Event::withoutEvents(function (): Event {
        return Event::query()->create([
            'title' => 'Example event',
            'capacity' => 25,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDay(),
            'show_waitlist_position' => false,
            'show_capacity_data' => false,
            'registration_expiration_minutes' => 30,
        ]);
    });

    $schema = EventForm::configure(Schema::make($page)->model($page->record));

    $fields = array_keys($schema->getFlatFields(withHidden: true));

    expect($fields)->toContain(
        'accent_color_title_and_button',
        'accent_color_required_and_hover',
        'accent_color_label_and_radio',
        'accent_color_section_title',
    );
});
