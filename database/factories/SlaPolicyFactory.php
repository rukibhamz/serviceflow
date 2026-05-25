<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SlaPolicy;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlaPolicy>
 */
class SlaPolicyFactory extends Factory
{
    protected $model = SlaPolicy::class;

    public function definition(): array
    {
        return [
            'tenant_id'          => Tenant::factory(),
            'name'               => fake()->words(3, true) . ' SLA',
            'priority'           => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'ticket_type'        => null,
            'response_minutes'   => fake()->randomElement([30, 60, 120, 240]),
            'resolution_minutes' => fake()->randomElement([240, 480, 960, 1440]),
            'business_hours'     => null,
            'is_default'         => false,
            'is_active'          => true,
        ];
    }

    /**
     * Default SLA policy (is_default = true).
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * SLA policy scoped to a specific priority.
     */
    public function forPriority(string $priority = 'high'): static
    {
        return $this->state(fn (array $attributes) => [
            'priority'   => $priority,
            'is_default' => false,
        ]);
    }
}
