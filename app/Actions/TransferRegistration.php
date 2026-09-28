<?php

namespace App\Actions;

use App\Enums\RegistrationStates;
use App\Exceptions\RegistrationTransferException;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\Invite;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class TransferRegistration
{
    /**
     * @return array{
     *     sourceEvent: Event,
     *     targetEvent: Event,
     *     fieldMap: array<int, int>,
     *     optionMap: array<int, int>,
     *     valueCount: int
     * }
     */
    public function validate(Registration $registration, Event $targetEvent): array
    {
        $sourceEvent = $registration->event;

        if ($sourceEvent === null) {
            throw new RegistrationTransferException('The registration has no source event.');
        }

        if ($sourceEvent->is($targetEvent)) {
            throw new RegistrationTransferException('The target event must differ from the source event.');
        }

        $sourceForm = $sourceEvent->form;
        $targetForm = $targetEvent->form;

        if ($sourceForm === null || $targetForm === null) {
            throw new RegistrationTransferException('Both events must have a form.');
        }

        if ($sourceForm->base_price_cents !== $targetForm->base_price_cents) {
            throw new RegistrationTransferException('The event form base prices are different.');
        }

        if ($registration->price_cents !== $this->formPrice($sourceForm, $registration)) {
            throw new RegistrationTransferException('The registration price does not match its source form.');
        }

        if ($this->isCapacityReserved($registration) && $targetEvent->availableCapacity() < 1) {
            throw new RegistrationTransferException('The target event has no capacity for this registration.');
        }

        $sourceFields = $sourceForm->sections->flatMap->fields->keyBy('name');
        $targetFields = $targetForm->sections->flatMap->fields->keyBy('name');

        if ($sourceFields->keys()->sort()->values()->all() !== $targetFields->keys()->sort()->values()->all()) {
            throw new RegistrationTransferException('The event forms do not contain the same fields.');
        }

        $fieldMap = [];
        $optionMap = [];

        foreach ($sourceFields as $name => $sourceField) {
            /** @var FormField $targetField */
            $targetField = $targetFields->get($name);

            if (! $this->fieldsMatch($sourceField, $targetField)) {
                throw new RegistrationTransferException("The form field '{$name}' is different.");
            }

            $sourceDependency = $sourceFields->firstWhere('id', $sourceField->dependency_field_id);
            $targetDependency = $targetFields->firstWhere('id', $targetField->dependency_field_id);

            if (($sourceDependency?->name !== $targetDependency?->name)
                || $sourceField->dependency_equals !== $targetField->dependency_equals) {
                throw new RegistrationTransferException("The dependency for form field '{$name}' is different.");
            }

            if ($sourceDependency !== null) {
                $sourceDependencyOption = $sourceDependency->options->firstWhere('id', $sourceField->dependency_option_id);
                $targetDependencyOption = $targetDependency?->options->firstWhere('id', $targetField->dependency_option_id);

                if ($sourceDependencyOption?->value !== $targetDependencyOption?->value) {
                    throw new RegistrationTransferException("The dependency option for form field '{$name}' is different.");
                }
            }

            $fieldMap[$sourceField->id] = $targetField->id;

            $sourceOptions = $sourceField->options->keyBy('value');
            $targetOptions = $targetField->options->keyBy('value');

            if ($sourceOptions->keys()->sort()->values()->all() !== $targetOptions->keys()->sort()->values()->all()) {
                throw new RegistrationTransferException("The options for form field '{$name}' are different.");
            }

            foreach ($sourceOptions as $value => $sourceOption) {
                /** @var FormFieldOption $targetOption */
                $targetOption = $targetOptions->get($value);

                if ($sourceOption->label !== $targetOption->label
                    || $sourceOption->price_cents !== $targetOption->price_cents
                    || $sourceOption->sort_order !== $targetOption->sort_order) {
                    throw new RegistrationTransferException("The option '{$value}' for form field '{$name}' is different.");
                }

                $optionMap[$sourceOption->id] = $targetOption->id;
            }
        }

        $sourceEmailName = $sourceFields->firstWhere('id', $sourceForm->email_field_id)?->name;
        $targetEmailName = $targetFields->firstWhere('id', $targetForm->email_field_id)?->name;
        $sourceNameName = $sourceFields->firstWhere('id', $sourceForm->name_field_id)?->name;
        $targetNameName = $targetFields->firstWhere('id', $targetForm->name_field_id)?->name;

        if ($sourceEmailName !== $targetEmailName || $sourceNameName !== $targetNameName) {
            throw new RegistrationTransferException('The designated name or email fields are different.');
        }

        foreach ($registration->registrationValues as $registrationValue) {
            if (! isset($fieldMap[$registrationValue->field_id])) {
                throw new RegistrationTransferException('The registration contains an answer for an unknown form field.');
            }

            if ($registrationValue->option_id !== null && ! isset($optionMap[$registrationValue->option_id])) {
                throw new RegistrationTransferException('The registration contains an answer for an unknown form option.');
            }
        }

        $targetPrice = $this->formPrice($targetForm, $registration, $optionMap);
        if ($registration->price_cents !== $targetPrice) {
            throw new RegistrationTransferException('The registration price would differ in the target event.');
        }

        return [
            'sourceEvent' => $sourceEvent,
            'targetEvent' => $targetEvent,
            'fieldMap' => $fieldMap,
            'optionMap' => $optionMap,
            'valueCount' => $registration->registrationValues->count(),
        ];
    }

    public function execute(Registration $registration, Event $targetEvent): void
    {
        DB::transaction(function () use ($registration, $targetEvent): void {
            $lockedRegistration = Registration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);

            Event::query()->lockForUpdate()->findOrFail($lockedRegistration->event_id);
            $lockedTargetEvent = Event::query()->lockForUpdate()->findOrFail($targetEvent->id);
            $lockedPlan = $this->validate($lockedRegistration, $lockedTargetEvent);

            foreach ($lockedRegistration->registrationValues as $registrationValue) {
                $registrationValue->update([
                    'field_id' => $lockedPlan['fieldMap'][$registrationValue->field_id],
                    'option_id' => $registrationValue->option_id === null
                        ? null
                        : $lockedPlan['optionMap'][$registrationValue->option_id],
                ]);
            }

            $lockedRegistration->update(['event_id' => $lockedTargetEvent->id]);

            Invite::query()
                ->where('used_registration_id', $lockedRegistration->id)
                ->update(['event_id' => $lockedTargetEvent->id]);
        });
    }

    private function fieldsMatch(FormField $sourceField, FormField $targetField): bool
    {
        return $sourceField->label === $targetField->label
            && $sourceField->placeholder === $targetField->placeholder
            && $sourceField->type === $targetField->type
            && $sourceField->html === $targetField->html
            && $sourceField->hide_option_price === $targetField->hide_option_price
            && $sourceField->width === $targetField->width
            && $sourceField->required === $targetField->required
            && $sourceField->sort_order === $targetField->sort_order;
    }

    /**
     * @param  array<int, int>  $fieldMap
     * @param  array<int, int>  $optionMap
     */
    private function formPrice(Form $form, Registration $registration, array $optionMap = []): int
    {
        $values = $registration->registrationValues->keyBy('field_id');

        return $form->base_price_cents + $values->sum(function ($value) use ($optionMap): int {
            if ($value->option_id === null) {
                return 0;
            }

            $optionId = $optionMap[$value->option_id] ?? $value->option_id;

            return (int) FormFieldOption::query()->whereKey($optionId)->value('price_cents');
        });
    }

    private function isCapacityReserved(Registration $registration): bool
    {
        return in_array($registration->currentState?->type, RegistrationStates::reservedStates(), true);
    }
}
