<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationLog>
 */
class AutomationLogFactory extends Factory
{
    protected $model = AutomationLog::class;

    public function definition(): array
    {
        return [
            'automation_id'        => Automation::factory(),
            'ticket_id'            => Ticket::factory(),
            'conditions_snapshot'  => [
                'operator'   => 'AND',
                'conditions' => [],
            ],
            'actions_executed'     => [
                ['type' => 'change_status', 'value' => 'in_progress'],
            ],
            'result'               => 'success',
        ];
    }

    /**
     * Successful automation log.
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'success',
        ]);
    }

    /**
     * Error automation log.
     */
    public function error(string $message = 'An unexpected error occurred'): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => 'error: ' . $message,
        ]);
    }
}
