<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Automation;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Automation>
 */
class AutomationFactory extends Factory
{
    protected $model = Automation::class;

    public function definition(): array
    {
        return [
            'tenant_id'     => Tenant::factory(),
            'name'          => fake()->words(3, true),
            'trigger_event' => fake()->randomElement(['ticket.created', 'ticket.updated', 'ticket.closed']),
            'conditions'    => [
                'operator'   => 'AND',
                'conditions' => [],
            ],
            'actions'       => [
                ['type' => 'change_status', 'value' => 'in_progress'],
            ],
            'is_active'     => true,
            'run_count'     => 0,
            'last_run_at'   => null,
        ];
    }

    /**
     * Active automation rule.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Inactive automation rule.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
