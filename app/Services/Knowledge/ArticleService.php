<?php

namespace App\Services\Knowledge;

use App\Models\ArticleCategory;
use App\Models\KnowledgeArticle;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ArticleService
{
    public function create(array $data, User $author): KnowledgeArticle
    {
        $this->validate($data);
        $this->enforceTeamScope($data, $author);

        $slug = $this->generateUniqueSlug($data['title']);

        $article = KnowledgeArticle::create([
            'title'       => $data['title'],
            'slug'        => $slug,
            'body'        => $data['body'],
            'team_id'     => $data['team_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'status'      => $data['status'] ?? 'draft',
            'author_id'   => $author->id,
        ]);

        $article->versions()->create([
            'title'     => $article->title,
            'body'      => $article->body,
            'editor_id' => $author->id,
        ]);

        return $article;
    }

    public function update(KnowledgeArticle $article, array $data, User $editor): KnowledgeArticle
    {
        $this->validate($data, partial: true);
        $this->enforceTeamScope($data, $editor);

        if (isset($data['title']) && $data['title'] !== $article->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $article->id);
        }

        $article->fill($data);
        $article->save();

        $article->versions()->create([
            'title'     => $article->title,
            'body'      => $article->body,
            'editor_id' => $editor->id,
        ]);

        return $article;
    }

    public function publish(KnowledgeArticle $article): KnowledgeArticle
    {
        $article->status = 'published';
        $article->save();

        return $article;
    }

    public function archive(KnowledgeArticle $article): KnowledgeArticle
    {
        $article->status = 'archived';
        $article->save();

        return $article;
    }

    public function delete(KnowledgeArticle $article): void
    {
        $article->delete();
    }

    public function incrementViewCount(KnowledgeArticle $article): void
    {
        $article->timestamps = false;
        $article->increment('view_count');
        $article->timestamps = true;
    }

    public function vote(KnowledgeArticle $article, bool $helpful): void
    {
        if ($helpful) {
            $article->increment('helpful_votes');
        } else {
            $article->increment('unhelpful_votes');
        }
    }

    private function validate(array $data, bool $partial = false): void
    {
        $rules = [
            'title'       => 'required|string|max:255',
            'body'        => 'required|string',
            'category_id' => 'nullable|exists:article_categories,id',
            'status'      => 'nullable|in:draft,published,archived',
        ];

        if ($partial) {
            $rules = array_intersect_key($rules, $data);
            // Make all rules optional for partial updates
            $rules = array_map(fn ($r) => str_replace('required|', 'sometimes|', $r), $rules);
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n    = 1;

        while (true) {
            $query = KnowledgeArticle::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function enforceTeamScope(array &$data, User $user): void
    {
        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';
        $allowedTeamIds = Team::query()
            ->where('team_lead_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id')
            ->unique()
            ->values();

        if (! empty($data['category_id'])) {
            $category = ArticleCategory::findOrFail((int) $data['category_id']);
            $categoryTeamId = $category->team_id ? (int) $category->team_id : null;

            if (! $isAdmin && ($categoryTeamId === null || ! $allowedTeamIds->contains($categoryTeamId))) {
                throw ValidationException::withMessages([
                    'category_id' => 'You can only use categories assigned to your team.',
                ]);
            }

            $data['team_id'] = $categoryTeamId;
            return;
        }

        if (! $isAdmin) {
            throw ValidationException::withMessages([
                'category_id' => 'Please select a team category before saving an article.',
            ]);
        }
    }
}
