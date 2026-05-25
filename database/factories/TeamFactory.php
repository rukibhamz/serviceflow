<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'tenant_id'              => Tenant::factory(),
            'name'                   => fake()->words(2, true) . ' Team',
            'description'            => fake()->sentence(),
            'inbound_email'          => null,
            'inbound_email_enabled'  => false,
        ];
    }

    /**
     * Team with an inbound email address configured and enabled.
     */
    public function withInboundEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'inbound_email'         => fake()->unique()->safeEmail(),
            'inbound_email_enabled' => true,
        ]);
    }
}
