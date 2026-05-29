<?php

use Tests\TestCase;

it('throttles the mollie webhook per ip', function () {
    /** @var TestCase $this */
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $this->postJson(route('webhooks.mollie'), [
            'id' => 'test-payment',
        ])->assertOk();
    }

    $this->postJson(route('webhooks.mollie'), [
        'id' => 'test-payment',
    ])->assertTooManyRequests();
});
