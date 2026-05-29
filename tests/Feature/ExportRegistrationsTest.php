<?php

use App\Actions\ExportRegistrations;
use App\Models\Registration;
use Tests\TestCase;

it('escapes spreadsheet formulas in exported csv cells', function (string $dangerousValue) {
    /** @var TestCase $this */
    $event = eventWithRegistrationForm();

    $registration = Registration::factory()->create([
        'event_id' => $event->id,
    ]);

    $nameFieldId = fieldId($event, 'name');

    $registration->registrationValues()
        ->where('field_id', $nameFieldId)
        ->update([
            'value' => $dangerousValue,
        ]);

    $registration->refresh()->load('registrationValues');
    $event->load('form.sections.fields');

    $csv = app()->make(ExportRegistrations::class)->execute($event, collect([$registration]), ',', true);

    expect($csv)->toContain("\"'{$dangerousValue}\"");
})->with([
    'equals' => '=2+2',
    'plus' => '+2+2',
    'minus' => '-2+2',
    'at' => '@sum(1,2)',
]);
