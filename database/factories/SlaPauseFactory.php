<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SlaPause;
use App\Models\SlaTimer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlaPause>
 */
class SlaPauseFactory extends Factory
{
    protected $model = SlaPause::class;

    public function definition(): array
    {
        return [
            'sla_timer_id' => SlaTimer::factory(),
            'paused_at'    => now()->subMinutes(30),
            'resumed_at'   => null,
            'reason'       => fake()->optional()->sentence(),
        ];
    }

    /**
     * Active pause — paused_at is set, resumed_at is null.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'paused_at'  => now()->subMinutes(30),
            'resumed_at' => null,
        ]);
    }

    /**
     * Resumed pause — both paused_at and resumed_at are set.
     */
    public function resumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'paused_at'  => now()->subMinutes(60),
            'resumed_at' => now()->subMinutes(15),
        ]);
    }
}
