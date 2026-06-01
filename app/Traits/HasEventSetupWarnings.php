<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * @method Model|null getRecord()
 */
trait HasEventSetupWarnings
{
    /** @var array{items: list<string>, tabCounts: array{general:int, form:int}, total:int} | null */
    protected ?array $eventSetupWarnings = null;

    /** @return array{items: list<string>, tabCounts: array{general:int, form:int}, total:int} */
    public function getEventSetupWarnings(): array
    {
        if ($this->eventSetupWarnings !== null) {
            return $this->eventSetupWarnings;
        }

        $event = $this->getRecord();

        $tabCounts = [
            'general' => 0,
            'form' => 0,
            'emails' => 0,
        ];

        $warnings = [];
        $form = $event?->form; // Event might be null on the CreateEvent page

        if (($event?->api_key_id) === null) {
            $warnings[] = __('admin.events.form.warnings.items.no_api_key');
            $tabCounts['general']++;
        }

        if (($form?->email_field_id) === null) {
            $warnings[] = __('admin.events.form.warnings.items.no_email_field');
            $tabCounts['form']++;
        }

        if (($form?->name_field_id) === null) {
            $warnings[] = __('admin.events.form.warnings.items.no_name_field');
            $tabCounts['form']++;
        }

        if ($form !== null && $form->fields->isEmpty()) {
            $warnings[] = __('admin.events.form.warnings.items.no_fields');
            $tabCounts['form']++;
        }

        $hasPricingOptions = $form !== null && $form->fields->contains(
            fn ($field): bool => ($field->type?->hasOptions() ?? false)
                && $field->options->contains(fn ($option): bool => (int) $option->price_cents > 0),
        );

        if (! $hasPricingOptions) {
            $warnings[] = __('admin.events.form.warnings.items.no_pricing_options');
            $tabCounts['form']++;
        }

        if (($event?->mailer_settings_id) === null) {
            $warnings[] = __('admin.events.form.warnings.items.no_mailer_settings');
            $tabCounts['emails']++;
        }

        return $this->eventSetupWarnings = [
            'items' => $warnings,
            'tabCounts' => $tabCounts,
            'total' => count($warnings),
        ];
    }

    public function hasEventSetupWarnings(): bool
    {
        return $this->getEventSetupWarnings()['total'] > 0;
    }

    public function getEventSetupWarningCountForTab(string $tab): ?int
    {
        $count = $this->getEventSetupWarnings()['tabCounts'][$tab] ?? 0;

        return $count > 0 ? $count : null;
    }

    public function getEventSetupWarningsDescription(): HtmlString
    {
        $items = $this->getEventSetupWarnings()['items'];
        $listItems = collect($items)
            ->map(fn (string $warning): string => '<li>'.e($warning).'</li>')
            ->implode('');

        return new HtmlString('<ul class="list-disc space-y-1 ps-6">'.$listItems.'</ul>');
    }
}
