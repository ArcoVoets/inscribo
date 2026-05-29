<?php

namespace Database\Factories;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\EventMailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventMailTemplate>
 */
class EventMailTemplateFactory extends Factory
{
    protected $model = EventMailTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'type' => fake()->randomElement(EventMailTemplateType::cases()),
            'subject' => fake()->sentence(),
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => fake()->sentences(asText: true),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
