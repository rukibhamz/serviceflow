<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ArticleService
{
    public function create(array $data, User $author): KnowledgeArticle
    {
        $this->validate($data);

        $slug = $this->generateUniqueSlug($data['title']);

        $article = KnowledgeArticle::create([
            'title'       => $data['title'],
            'slug'        => $slug,
            'body'        => $data['body'],
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
}
