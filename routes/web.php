<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/events/{event}/preview', [RegistrationController::class, 'preview'])->name('events.preview');
Route::get('/events/{event}/register', [RegistrationController::class, 'create'])->name('events.register');
Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('events.register.store');

Route::scopeBindings()
    ->prefix('events/{event}/registrations/{registration}')
    ->name('events.register.')
    ->group(function () {
        Route::get('/status', [RegistrationController::class, 'status'])
            ->name('status');
        Route::get('/checkout', [RegistrationController::class, 'checkout'])
            ->name('checkout');
    });
