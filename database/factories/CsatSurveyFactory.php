<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CsatSurvey;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CsatSurvey>
 */
class CsatSurveyFactory extends Factory
{
    protected $model = CsatSurvey::class;

    public function definition(): array
    {
        return [
            'ticket_id'    => Ticket::factory(),
            'requester_id' => User::factory(),
            'token'        => Str::random(64),
            'rating'       => null,
            'comment'      => null,
            'sent_at'      => now(),
            'responded_at' => null,
        ];
    }

    /**
     * Pending survey — sent but not yet responded to.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating'       => null,
            'comment'      => null,
            'sent_at'      => now()->subHours(2),
            'responded_at' => null,
        ]);
    }

    /**
     * Responded survey — rating and responded_at are set.
     */
    public function responded(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating'       => fake()->numberBetween(1, 5),
            'comment'      => fake()->optional()->sentence(),
            'sent_at'      => now()->subDays(1),
            'responded_at' => now()->subHours(1),
        ]);
    }
}
