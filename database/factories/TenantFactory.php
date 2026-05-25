<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name'      => fake()->company(),
            'subdomain' => Str::slug(fake()->unique()->word() . '-' . fake()->numerify('###')),
            'is_active' => true,
            'settings'  => [],
        ];
    }

    /**
     * Active tenant (default behaviour, explicit state for clarity).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Suspended tenant — is_active = false.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
