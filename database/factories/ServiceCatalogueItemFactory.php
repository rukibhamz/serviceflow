<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceCatalogueItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCatalogueItem>
 */
class ServiceCatalogueItemFactory extends Factory
{
    protected $model = ServiceCatalogueItem::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'tenant_id'   => Tenant::factory(),
            'team_id'     => null,
            'created_by'  => User::factory(),
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numerify('####'),
            'name'        => $name,
            'description' => fake()->sentence(),
            'type'        => fake()->randomElement(['service_request', 'incident', 'change']),
            'priority'    => fake()->randomElement(['low', 'medium', 'high']),
            'fields'      => null,
            'is_active'   => true,
        ];
    }

    /**
     * Active catalogue item.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Inactive catalogue item.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
