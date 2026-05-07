<?php

namespace App\Livewire\Knowledge;

use App\Models\ArticleCategory;
use App\Models\KnowledgeArticle;
use App\Models\Team;
use App\Services\Knowledge\ArticleService;
use Illuminate\Support\Str;
use Livewire\Component;

class ArticleEditor extends Component
{
    public ?KnowledgeArticle $article = null;

    public string $title = '';
    public string $body = '';
    public string $categoryId = '';
    public string $status = 'draft';
    public string $newCategoryName = '';
    public string $newCategoryTeamId = '';
    public array $allowedTeamIds = [];

    public function mount(?KnowledgeArticle $article = null): void
    {
        $this->allowedTeamIds = $this->resolveAllowedTeamIds();
        if (! $this->isAdmin() && count($this->allowedTeamIds) === 1) {
            $this->newCategoryTeamId = (string) $this->allowedTeamIds[0];
        }

        if ($article && $article->exists) {
            $this->article    = $article;
            $this->title      = $article->title;
            $this->body       = $article->body;
            $this->categoryId = (string) ($article->category_id ?? '');
            $this->status     = $article->status;
        }
    }

    public function save(ArticleService $service): void
    {
        $this->validate([
            'title' => 'required|min:3',
            'body'  => 'required|min:10',
        ]);

        $data = [
            'title'       => $this->title,
            'body'        => $this->body,
            'category_id' => $this->categoryId ?: null,
            'status'      => $this->status,
        ];

        if ($this->article && $this->article->exists) {
            $this->article = $service->update($this->article, $data, auth()->user());
        } else {
            $this->article = $service->create($data, auth()->user());
        }

        session()->flash('success', 'Article saved successfully.');
    }

    public function createCategory(): void
    {
        $rules = [
            'newCategoryName' => 'required|string|min:2|max:120',
        ];

        if ($this->isAdmin()) {
            $rules['newCategoryTeamId'] = 'required|integer|exists:teams,id';
        }

        $this->validate($rules);

        $teamId = $this->isAdmin()
            ? (int) $this->newCategoryTeamId
            : (int) ($this->allowedTeamIds[0] ?? 0);

        if ($teamId < 1 || (! $this->isAdmin() && ! in_array($teamId, $this->allowedTeamIds, true))) {
            $this->addError('newCategoryTeamId', 'Invalid team for this category.');
            return;
        }

        $baseSlug = Str::slug($this->newCategoryName);
        $slug = $baseSlug;
        $n = 1;
        while (ArticleCategory::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . (++$n);
        }

        $category = ArticleCategory::create([
            'name' => $this->newCategoryName,
            'slug' => $slug,
            'team_id' => $teamId,
            'parent_id' => null,
            'sort_order' => 0,
        ]);

        $this->categoryId = (string) $category->id;
        $this->newCategoryName = '';
        session()->flash('success', 'Category created.');
    }

    public function publish(ArticleService $service): void
    {
        if ($this->article && $this->article->exists) {
            $this->article = $service->publish($this->article);
            $this->status  = 'published';
            session()->flash('success', 'Article published.');
        }
    }

    public function render()
    {
        $categories = ArticleCategory::query()
            ->with('team')
            ->when(! $this->isAdmin(), fn ($q) => $q->whereIn('team_id', $this->allowedTeamIds))
            ->orderBy('name')
            ->get();

        $teams = Team::query()
            ->when(! $this->isAdmin(), fn ($q) => $q->whereIn('id', $this->allowedTeamIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.knowledge.article-editor', compact('categories', 'teams'));
    }

    private function isAdmin(): bool
    {
        $user = auth()->user();
        return (bool) ($user && ($user->hasRole('admin') || $user->role === 'admin'));
    }

    private function resolveAllowedTeamIds(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        if ($this->isAdmin()) {
            return Team::orderBy('name')->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return Team::query()
            ->where('team_lead_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
