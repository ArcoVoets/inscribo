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
            self::query()->whereKey($event->form->id)->delete();
        }

        $replica = $this->replicate(['event_id', 'email_field_id', 'name_field_id']);
        $replica->event_id = $event->id;
        $replica->save();

        $replicatedEmailFieldId = null;
        $replicatedNameFieldId = null;
        $fieldIdMap = [];
        $optionIdMap = [];
        $fieldReplicas = [];

        foreach ($this->sections as $section) {
            $sectionReplica = $section->replicate(['form_id']);
            $sectionReplica->form_id = $replica->id;
            $replica->sections()->save($sectionReplica);

            foreach ($section->fields as $field) {
                $fieldReplica = $field->replicate(['default_option_id', 'dependency_field_id', 'dependency_option_id', 'form_id', 'section_id']);
                $fieldReplica->form_id = $replica->id;
                $fieldReplica->section_id = $sectionReplica->id;
                $sectionReplica->fields()->save($fieldReplica);
                $fieldIdMap[$field->id] = $fieldReplica->id;
                $fieldReplicas[$field->id] = $fieldReplica;

                foreach ($field->options as $option) {
                    $optionReplica = $option->replicate(['field_id']);
                    $optionReplica->field_id = $fieldReplica->id;
                    $fieldReplica->options()->save($optionReplica);
                    $optionIdMap[$option->id] = $optionReplica->id;
                }

                if ($this->email_field_id === $field->id) {
                    $replicatedEmailFieldId = $fieldReplica->id;
                }
                if ($this->name_field_id === $field->id) {
                    $replicatedNameFieldId = $fieldReplica->id;
                }
            }

        }

        foreach ($this->fields as $field) {
            $updates = [];

            if ($field->default_option_id !== null) {
                $updates['default_option_id'] = $optionIdMap[$field->default_option_id];
            }

            if ($field->dependency_field_id !== null) {
                $updates['dependency_field_id'] = $fieldIdMap[$field->dependency_field_id];
                $updates['dependency_option_id'] = $optionIdMap[$field->dependency_option_id];
                $updates['dependency_equals'] = $field->dependency_equals;
            }

            if ($updates !== []) {
                $fieldReplicas[$field->id]->update($updates);
            }
        }

        $replica->update([
            'email_field_id' => $replicatedEmailFieldId,
            'name_field_id' => $replicatedNameFieldId,
        ]);
    }
}
