<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\KnowledgeArticle;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeArticle>
 */
class KnowledgeArticleFactory extends Factory
{
    protected $model = KnowledgeArticle::class;

    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'tenant_id'       => Tenant::factory(),
            'title'           => $title,
            'slug'            => Str::slug($title) . '-' . fake()->unique()->numerify('####'),
            'body'            => fake()->paragraphs(3, true),
            'status'          => 'draft',
            'category_id'     => null,
            'author_id'       => User::factory(),
            'view_count'      => 0,
            'helpful_votes'   => 0,
            'unhelpful_votes' => 0,
            'expires_at'      => null,
        ];
    }

    /**
     * Draft article.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Published article.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Archived article.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
