<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChangeApprover;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChangeApprover>
 */
class ChangeApproverFactory extends Factory
{
    protected $model = ChangeApprover::class;

    public function definition(): array
    {
        return [
            'ticket_id'  => Ticket::factory()->change(),
            'user_id'    => User::factory(),
            'decision'   => null,
            'comment'    => null,
            'token'      => Str::random(64),
            'decided_at' => null,
        ];
    }

    /**
     * Approved change approver.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision'   => 'approved',
            'decided_at' => now(),
            'comment'    => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Rejected change approver.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision'   => 'rejected',
            'decided_at' => now(),
            'comment'    => fake()->sentence(),
        ]);
    }

    /**
     * Pending change approver (no decision yet).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'decision'   => null,
            'decided_at' => null,
            'comment'    => null,
        ]);
    }
}
