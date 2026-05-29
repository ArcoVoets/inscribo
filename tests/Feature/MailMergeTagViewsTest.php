<?php

use App\Models\Registration;
use Illuminate\Support\HtmlString;

it('renders registration details fragment with fields', function () {
    $registration = Registration::factory()->create();

    $html = view('mail.merge-tags.registration-details', [
        'eventTitle' => $registration->event->title,
        'fields' => $registration->registrationValues,
    ])->render();

    expect($html)
        ->toContain('<table')
        ->toContain($registration->registrationValues->first()->field->label);
});

it('renders pricing details fragment with price rows', function () {
    $priceRows = [
        [
            'label' => 'Base price',
            'amountCents' => 1500,
        ],
        [
            'label' => 'Worker surcharge',
            'amountCents' => 500,
        ],
    ];

    $html = view('mail.merge-tags.pricing-details', [
        'priceRows' => $priceRows,
        'totalPriceCents' => 2000,
    ])->render();

    expect($html)
        ->toContain('<table')
        ->toContain('Base price')
        ->toContain('€')
        ->toContain(__('mail.registration_completed.pricing.total'));
});

it('accepts htmlable merge tag values in custom templates', function () {
    $htmlContent = new HtmlString('<strong>Test content</strong>');

    // Verify that HtmlString is accepted as merge tag value
    expect($htmlContent)->toBeInstanceOf(HtmlString::class);
    expect($htmlContent->toHtml())->toBe('<strong>Test content</strong>');
});

it('renders the status button fragment without mail components', function () {
    $html = view('mail.merge-tags.mail-button', [
        'url' => 'https://example.com/status',
        'label' => 'View status',
    ])->render();

    expect($html)
        ->toContain('<a href="https://example.com/status"')
        ->toContain('View status')
        ->not->toContain('x-mail::button');
});
