<?php

namespace App\Observers;

use App\Models\FormSection;

class FormSectionObserver
{
    public function deleting(FormSection $section): void
    {
        throw_if(
            $section->fields()->whereHas('registrationValues')->exists(),
            \RuntimeException::class, 'Cannot delete section: registrations exist using fields in this section.'
        );
    }
}
