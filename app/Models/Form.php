<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'base_price_cents' => 'integer',
        ];
    }

    protected $attributes = [
        'base_price_cents' => 0,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class, 'form_id')->orderBy('sort_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class)->orderBy('sort_order');
    }

    /**
     * Replicate a form, including all of it's sections, fields, field options and default field options.
     * The replicated from is associated with the given event. If the event already has a form, it will be deleted.
     */
    public function deepReplicate(Event $event): void
    {
        $event->refresh();
        if ($event->form !== null) {
            $event->form->delete();
        }

        $replica = $this->replicate(['event_id', 'email_field_id', 'name_field_id']);
        $replica->event_id = $event->id;
        $replica->save();

        $replicatedEmailFieldId = null;
        $replicatedNameFieldId = null;

        foreach ($this->sections as $section) {
            $sectionReplica = $section->replicate(['form_id']);
            $sectionReplica->form_id = $replica->id;
            $replica->sections()->save($sectionReplica);

            foreach ($section->fields as $field) {
                $fieldReplica = $field->replicate(['default_option_id', 'form_id', 'section_id']);
                $fieldReplica->form_id = $replica->id;
                $fieldReplica->section_id = $sectionReplica->id;
                $sectionReplica->fields()->save($fieldReplica);

                $defaultOptionId = null;
                foreach ($field->options as $option) {
                    $optionReplica = $option->replicate(['field_id']);
                    $optionReplica->field_id = $fieldReplica->id;
                    $fieldReplica->options()->save($optionReplica);

                    if ($option->id === $field->default_option_id) {
                        $defaultOptionId = $optionReplica->id;
                    }
                }

                if ($defaultOptionId !== null) {
                    $fieldReplica->update([
                        'default_option_id' => $defaultOptionId,
                    ]);
                }

                if ($this->email_field_id === $field->id) {
                    $replicatedEmailFieldId = $fieldReplica->id;
                }
                if ($this->name_field_id === $field->id) {
                    $replicatedNameFieldId = $fieldReplica->id;
                }
            }

        }

        $replica->update([
            'email_field_id' => $replicatedEmailFieldId,
            'name_field_id' => $replicatedNameFieldId,
        ]);
    }
}
