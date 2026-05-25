<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Tenant;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'tenant_id'     => Tenant::factory(),
            'name'          => fake()->words(2, true),
            'asset_tag'     => fake()->unique()->bothify('ASSET-####'),
            'type'          => fake()->randomElement(['hardware', 'software', 'network', 'peripheral']),
            'serial_number' => fake()->optional()->bothify('SN-########'),
            'assigned_to'   => null,
            'status'        => 'active',
            'purchased_at'  => fake()->optional()->dateTimeBetween('-3 years', '-1 month'),
            'eol_at'        => fake()->optional()->dateTimeBetween('+1 year', '+5 years'),
            'meta'          => null,
        ];
    }

    /**
     * Active asset.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Retired asset.
     */
    public function retired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'retired',
        ]);
    }

    /**
     * Asset linked to a ticket (via afterCreating).
     */
    public function withTicket(): static
    {
        return $this->afterCreating(function (Asset $asset) {
            $ticket = Ticket::factory()->create(['tenant_id' => $asset->tenant_id]);
            $asset->tickets()->attach($ticket->id);
        });
    }
}
