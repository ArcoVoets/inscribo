<?php

use App\Models\FormField;
use App\Models\Registration;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Tests\TestCase;

it('ignores a hidden dependent field during registration submission', function () {
    /** @var TestCase $this */
    $event = eventWithRegistrationForm();

    $section = $event->form->sections()->sole();
    $participantTypeField = $event->form->fields()->where('name', 'participant_type')->sole();
    $workerOption = $participantTypeField->options()->where('value', 'worker')->sole();

    $companyField = FormField::factory()->create([
        'form_id' => $event->form->id,
        'section_id' => $section->id,
        'label' => 'Company',
        'name' => 'company',
        'type' => 'text',
        'required' => true,
        'sort_order' => 2,
        'dependency_field_id' => $participantTypeField->id,
        'dependency_option_id' => $workerOption->id,
        'dependency_equals' => true,
    ]);

    $payload = registrationPayload($event);

    $this->post(route('events.register.store', ['event' => $event->id]), $payload)
        ->assertRedirect();

    $registration = Registration::query()
        ->where('event_id', $event->id)
        ->whereHas('registrationValues', fn (Builder $query): Builder => $query
            ->where('field_id', fieldId($event, 'email'))
        )
        ->sole();

    expect($registration->registrationValues()->where('field_id', $companyField->id)->exists())->toBeFalse();
});

it('requires a dependent field when its condition is met', function () {
    /** @var TestCase $this */
    $event = eventWithRegistrationForm();

    $section = $event->form->sections()->sole();
    $participantTypeField = $event->form->fields()->where('name', 'participant_type')->sole();
    $workerOption = $participantTypeField->options()->where('value', 'worker')->sole();

    $companyField = FormField::factory()->create([
        'form_id' => $event->form->id,
        'section_id' => $section->id,
        'label' => 'Company',
        'name' => 'company',
        'type' => 'text',
        'required' => true,
        'sort_order' => 2,
        'dependency_field_id' => $participantTypeField->id,
        'dependency_option_id' => $workerOption->id,
        'dependency_equals' => true,
    ]);

    $payload = registrationPayload($event);
    $payload['fields'][$participantTypeField->id] = 'worker';
    unset($payload['fields'][$companyField->id]);

    $this->post(route('events.register.store', ['event' => $event->id]), $payload)
        ->assertSessionHasErrors([(string) $companyField->id]);
});
