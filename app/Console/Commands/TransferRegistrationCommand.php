<?php

namespace App\Console\Commands;

use App\Actions\TransferRegistration;
use App\Exceptions\RegistrationTransferException;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('registration:transfer {registration : The registration ID} {target-event : The target event ID} {--dry-run : Validate without changing anything}')]
#[Description('Transfer a registration to another event when the forms and price are identical')]
class TransferRegistrationCommand extends Command
{
    public function handle(TransferRegistration $transferRegistration): int
    {
        $registration = Registration::query()->findOrFail((int) $this->argument('registration'));
        $targetEvent = Event::query()->findOrFail((int) $this->argument('target-event'));

        try {
            $plan = $transferRegistration->validate($registration, $targetEvent);
        } catch (RegistrationTransferException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Validation passed: {$plan['valueCount']} registration values can be transferred.");

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        if (! $this->confirm("Transfer registration {$registration->id} to event {$targetEvent->id}?")) {
            $this->info('Transfer cancelled.');

            return self::SUCCESS;
        }

        try {
            $transferRegistration->execute($registration, $targetEvent);
        } catch (RegistrationTransferException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info('Registration transferred.');

        return self::SUCCESS;
    }
}
