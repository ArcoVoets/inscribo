<?php

namespace App\Observers;

use App\Models\FormField;

class FormFieldObserver
{
    public function creating(FormField $formField): void
    {
        if ($formField->form_id === null) {
            $formField->form_id = $formField->section->form_id;
        }
    }

    public function saving(FormField $formField): void
    {
        if (! $formField->type->hasOptions()) {
            $formField->default_option_id = null;
            $formField->hide_option_price = false;
            $formField->options()->delete();
        }
    }

    public function deleting(FormField $formField): void
    {
        throw_if(
            $formField->registrationValues()->exists(),
            \RuntimeException::class, 'Cannot delete field: registrations exist using this field.'
        );
    }
}
