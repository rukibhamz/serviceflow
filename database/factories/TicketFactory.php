<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SlaTimer;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $tenant = Tenant::factory()->create();

        return [
            'tenant_id'    => $tenant->id,
            'ulid'         => (string) Str::ulid(),
            'subject'      => fake()->sentence(6),
            'description'  => fake()->paragraph(),
            'status'       => 'open',
            'priority'     => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'type'         => 'incident',
            'source'       => 'web',
            'requester_id' => User::factory()->for($tenant),
            'assignee_id'  => null,
            'team_id'      => null,
            'custom_fields'=> null,
            'closed_at'    => null,
        ];
    }

    /**
     * Open ticket.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'    => 'open',
            'closed_at' => null,
        ]);
    }

    /**
     * Closed ticket.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'    => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Ticket with SLA timers (created via afterCreating).
     */
    public function withSlaTimers(): static
    {
        return $this->afterCreating(function (Ticket $ticket) {
            SlaTimer::factory()->active()->for($ticket)->create(['type' => 'response']);
            SlaTimer::factory()->active()->for($ticket)->create(['type' => 'resolution']);
        });
    }

    /**
     * Ticket with media attachments (placeholder — Spatie MediaLibrary handles actual files).
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => []);
    }

    /**
     * Change management ticket.
     */
    public function change(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'                  => 'change',
            'change_type'           => 'normal',
            'risk_level'            => 'medium',
            'cab_approval_required' => true,
        ]);
    }

    /**
     * Problem management ticket.
     */
    public function problem(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'problem',
        ]);
    }
}
