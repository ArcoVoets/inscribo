<?php

use App\Http\Controllers\MollieWebhookController;
use Illuminate\Support\Facades\Route;

Route::name('webhooks.mollie')
    ->post('webhooks/mollie', MollieWebhookController::class)
    ->middleware('throttle:mollie-webhook');
