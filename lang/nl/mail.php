<?php

return [
    'registration_submitted_payment_pending' => [
        'subject' => 'Inschrijving ontvangen: :event_title; betaling in afwachting',
    ],
    'waitlisted' => [
        'subject' => 'Je staat op de wachtlijst voor :event_title',
    ],
    'invited_from_waitlist' => [
        'subject' => 'Je bent uitgenodigd van de wachtlijst voor :event_title; betaling in afwachting',
    ],
    'registration_completed' => [
        'subject' => 'Inschrijving voltooid: :event_title',
        'base_price' => 'Basisprijs',
        'table' => [
            'field' => 'Veld',
            'value' => 'Waarde',
        ],
        'pricing' => [
            'table' => [
                'item' => 'Item',
                'price' => 'Prijs',
            ],
            'total' => 'Totaal',
        ],
    ],
    'invited_to_register' => [
        'subject' => 'Je bent uitgenodigd om te registreren voor :event_title',
        'expires_at_sentence' => 'Deze uitnodiging verloopt op :expires_at.',
        'register_button' => 'Inschrijven',
    ],
    'status' => [
        'action' => 'Bekijk inschrijvingsstatus',
    ],
];
