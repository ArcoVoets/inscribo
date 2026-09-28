<?php

use App\Enums\RegistrationStates;
use App\Models\Registration;
use App\Models\RegistrationPayment;

it('transfers a paid registration when the forms and price match', function () {
    $sourceEvent = eventWithRegistrationForm();
    $targetEvent = eventWithRegistrationForm();
    $sourceEvent->form->deepReplicate($targetEvent);
    $targetEvent->refresh();

    $registration = Registration::factory()
        ->registered()
        ->create([
            'event_id' => $sourceEvent->id,
            'price_cents' => 1500,
        ]);

    $sourceParticipantField = $sourceEvent->form->fields()->where('name', 'participant_type')->sole();
    $sourceWorkerOption = $sourceParticipantField->options()->where('value', 'worker')->sole();
    $registration->registrationValues()
        ->where('field_id', $sourceParticipantField->id)
        ->update([
            'option_id' => $sourceWorkerOption->id,
            'option_price_cents' => 1500,
        ]);
    $payment = RegistrationPayment::factory()->create(['registration_id' => $registration->id]);

    $this->artisan('registration:transfer', [
        'registration' => $registration->id,
        'target-event' => $targetEvent->id,
    ])
        ->expectsConfirmation("Transfer registration {$registration->id} to event {$targetEvent->id}?", 'yes')
        ->assertSuccessful();

    $registration->refresh();
    expect($registration->event_id)->toBe($targetEvent->id)
        ->and($registration->price_cents)->toBe(1500)
        ->and($registration->currentState->type)->toBe(RegistrationStates::Registered)
        ->and($registration->payments()->first()->is($payment))->toBeTrue();

    $targetParticipantField = $targetEvent->form->fields()->where('name', 'participant_type')->sole();
    $targetWorkerOption = $targetParticipantField->options()->where('value', 'worker')->sole();
    $transferredValue = $registration->registrationValues()
        ->where('field_id', $targetParticipantField->id)
        ->sole();

    expect($transferredValue->option_id)->toBe($targetWorkerOption->id);
});

it('rejects a transfer when a priced option differs', function () {
    $sourceEvent = eventWithRegistrationForm();
    $targetEvent = eventWithRegistrationForm();
    $sourceEvent->form->deepReplicate($targetEvent);
    $targetEvent->refresh();
    $targetWorkerOption = $targetEvent->form->fields()
        ->where('name', 'participant_type')
        ->sole()
        ->options()
        ->where('value', 'worker')
        ->sole();
    $targetWorkerOption->update(['price_cents' => 1600]);

    $registration = Registration::factory()->create([
        'event_id' => $sourceEvent->id,
        'price_cents' => 1500,
    ]);
    $sourceParticipantField = $sourceEvent->form->fields()->where('name', 'participant_type')->sole();
    $sourceWorkerOption = $sourceParticipantField->options()->where('value', 'worker')->sole();
    $registration->registrationValues()
        ->where('field_id', $sourceParticipantField->id)
        ->update([
            'option_id' => $sourceWorkerOption->id,
            'option_price_cents' => 1500,
        ]);

    $this->artisan('registration:transfer', [
        'registration' => $registration->id,
        'target-event' => $targetEvent->id,
        '--dry-run' => true,
    ])
        ->expectsOutput('The option \'worker\' for form field \'participant_type\' is different.')
        ->assertFailed();

    expect($registration->fresh()->event_id)->toBe($sourceEvent->id);
});
