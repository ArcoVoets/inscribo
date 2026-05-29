<?php

return [
    'registration_submitted_payment_pending' => [
        'subject' => 'Registration received: :event_title; payment pending',
    ],
    'waitlisted' => [
        'subject' => 'You are on the waitlisted for :event_title',
    ],
    'invited_from_waitlist' => [
        'subject' => 'You were invited from the waitlist for :event_title; payment pending',
    ],
    'registration_completed' => [
        'subject' => 'Registration completed: :event_title',
        'base_price' => 'Base price',
        'table' => [
            'field' => 'Field',
            'value' => 'Value',
        ],
        'pricing' => [
            'table' => [
                'item' => 'Item',
                'price' => 'Price',
            ],
            'total' => 'Total',
        ],
    ],
    'invited_to_register' => [
        'subject' => 'You are invited to register for :event_title',
        'expires_at_sentence' => 'This invitation expires on :expires_at.',
        'register_button' => 'Register',
    ],
    'status' => [
        'action' => 'View registration status',
    ],
];
