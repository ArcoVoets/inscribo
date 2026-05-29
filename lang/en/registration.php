<?php

return [
    'participant' => 'Participant',

    'participant_types' => [
        'student' => 'Student',
        'worker' => 'Worker',
    ],

    'states' => [
        'waitlisted' => 'Waitlisted',
        'payment_pending' => 'Payment pending',
        'registered' => 'Registered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'payment_expired' => 'Payment expired',
    ],

    'validation' => [
        'closed' => 'Registration is closed.',
        'upcoming' => 'Registration has not opened yet.',
    ],
];
