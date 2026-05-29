<?php

use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\FormSection;
use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function fieldId(Event $event, string $fieldName): int
{
    return $event->form->fields()->where('name', $fieldName)->sole()->id;
}

function eventWithRegistrationForm(array $eventAttributes = []): Event
{
    return Event::withoutEvents(function () use ($eventAttributes): Event {
        $event = Event::query()->create(array_merge([
            'title' => fake()->sentence(3),
            'capacity' => fake()->numberBetween(5, 50),
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDay(),
            'show_waitlist_position' => false,
            'show_capacity_data' => false,
            'home_url' => null,
            'registration_expiration_minutes' => 30,
        ], $eventAttributes));

        $form = Form::factory()->create([
            'event_id' => $event->id,
            'title' => $event->title.' Registration',
        ]);

        $section = FormSection::factory()->create([
            'form_id' => $form->id,
            'title' => 'Main',
            'sort_order' => 0,
        ]);

        $nameField = FormField::factory()->create([
            'form_id' => $form->id,
            'section_id' => $section->id,
            'label' => 'Name',
            'name' => 'name',
            'type' => 'text',
            'required' => true,
            'sort_order' => 0,
        ]);

        $emailField = FormField::factory()->create([
            'form_id' => $form->id,
            'section_id' => $section->id,
            'label' => 'Email',
            'name' => 'email',
            'type' => 'email',
            'required' => true,
            'sort_order' => 0,
        ]);

        $participantTypeField = FormField::factory()->create([
            'form_id' => $form->id,
            'section_id' => $section->id,
            'label' => 'Participant type',
            'name' => 'participant_type',
            'type' => 'select',
            'required' => true,
            'sort_order' => 1,
        ]);

        FormFieldOption::factory()->createMany([
            ['field_id' => $participantTypeField->id, 'label' => 'Student', 'value' => 'student', 'price_cents' => 0, 'sort_order' => 0],
            ['field_id' => $participantTypeField->id, 'label' => 'Worker', 'value' => 'worker', 'price_cents' => 1500, 'sort_order' => 1],
        ]);

        $form->update([
            'name_field_id' => $nameField->id,
            'email_field_id' => $emailField->id,
        ]);

        return $event->fresh(['form.sections.fields.options']);
    });
}

function registrationPayload(Event $event, ?Invite $invite = null, string $email = 'registrant@example.com'): array
{
    $event->refresh();

    $nameFieldId = fieldId($event, 'name');
    $emailFieldId = fieldId($event, 'email');
    $participantTypeField = $event->form->fields()->where('name', 'participant_type')->sole();

    $payload = [
        'fields' => [
            $nameFieldId => 'Invited Registrant',
            $emailFieldId => $email,
            $participantTypeField->id => 'student',
        ],
    ];

    if ($invite) {
        $payload['invite_id'] = $invite->id;
        $payload['invite_token'] = $invite->token;
    }

    return $payload;
}
