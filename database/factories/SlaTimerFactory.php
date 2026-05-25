<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SlaPolicy;
use App\Models\SlaTimer;
use App\Models\Tenant;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlaTimer>
 */
class SlaTimerFactory extends Factory
{
    protected $model = SlaTimer::class;

    public function definition(): array
    {
        return [
            'ticket_id'      => Ticket::factory(),
            'sla_policy_id'  => SlaPolicy::factory(),
            'type'           => fake()->randomElement(['response', 'resolution']),
            'due_at'         => now()->addHours(4),
            'paused_at'      => null,
            'paused_minutes' => 0,
            'breached'       => false,
            'stopped_at'     => null,
        ];
    }

    /**
     * Active (running) SLA timer — not breached, not stopped.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at'     => now()->addHours(4),
            'breached'   => false,
            'stopped_at' => null,
            'paused_at'  => null,
        ]);
    }

    /**
     * Breached SLA timer — due_at is in the past, breached = true.
     */
    public function breached(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at'     => now()->subHours(2),
            'breached'   => true,
            'stopped_at' => null,
        ]);
    }

    /**
     * Stopped SLA timer — stopped_at is set.
     */
    public function stopped(): static
    {
        return $this->state(fn (array $attributes) => [
            'stopped_at' => now(),
            'breached'   => false,
        ]);
    }
}
