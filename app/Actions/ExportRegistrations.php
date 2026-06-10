<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\FormField;
use App\Models\Registration;
use Illuminate\Support\Collection;

class ExportRegistrations
{
    /** @param Collection<int, Registration> $registrations */
    public function execute(Event $event, Collection $registrations, string $separator, bool $includeHeaders = true): string
    {
        $export = collect();

        // Get fields, ordered by section sort order and then field sort order
        $fields = $event->form->sections->flatMap->fields;
        $fields = $fields->filter(fn (FormField $field) => $field->type->hasValue());

        if ($includeHeaders) {
            $export->push($fields->pluck('name')->toArray());
        }

        foreach ($registrations as $registration) {
            $row = [];
            foreach ($fields as $field) {
                $value = $registration->registrationValues->firstWhere('field_id', $field->id)?->value ?? '';
                if ($field->type->shouldBeEscaped()) {
                    $value = $this->escapeCsvValue($value);
                }
                $row[] = $value;
            }
            $export->push($row);
        }

        $csv = $this->toCsv($export, $separator);

        return $csv;
    }

    private function toCsv(Collection $data, string $separator = ','): string
    {
        $csv = '';
        foreach ($data as $row) {
            $csv .= implode($separator, $row)."\n";
        }

        return $csv;
    }

    private function escapeCsvValue(mixed $value): string
    {
        $value = $this->escapeSpreadsheetFormula($value);

        // Surround by double quotes to allow for separators and newlines in the value,
        // and escape existing double quotes by doubling them. Also add an extra single
        // quote at the start to prevent Excel from interpreting values as formulas.
        return '"'.str_replace('"', '""', $value).'"';
    }

    private function escapeSpreadsheetFormula(mixed $value): string
    {
        $value = (string) $value;

        if (preg_match('/^[\t\r\n ]*[=+\-@]/', $value) === 1) {
            return "'{$value}";
        }

        return $value;
    }
}
