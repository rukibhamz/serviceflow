<?php

namespace App\Livewire\Knowledge;

use App\Models\ArticleCategory;
use App\Models\KnowledgeArticle;
use App\Models\Team;
use App\Services\Knowledge\ArticleSearchService;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryId = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $id): void
    {
        $this->categoryId = $this->categoryId === $id ? '' : $id;
        $this->resetPage();
    }

    public function render(ArticleSearchService $searchService)
    {
        $allowedTeamIds = $this->resolveAllowedTeamIds();
        $isAdmin = $this->isAdmin();

        if ($this->search !== '') {
            $searchResults = $searchService->search($this->search, 50);

            // Apply category filter on top of search results
            if ($this->categoryId !== '') {
                $searchResults = $searchResults->filter(
                    fn ($a) => (string) $a->category_id === $this->categoryId
                )->values();
            }

            if (! $isAdmin) {
                $searchResults = $searchResults->filter(
                    fn ($a) => $a->team_id !== null && in_array((int) $a->team_id, $allowedTeamIds, true)
                )->values();
            }

            // Manual pagination over the collection
            $page     = $this->getPage();
            $perPage  = 15;
            $total    = $searchResults->count();
            $items    = $searchResults->slice(($page - 1) * $perPage, $perPage)->values();
            $articles = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } else {
            $articles = KnowledgeArticle::query()
                ->with('category')
                ->where('status', 'published')
                ->when(! $isAdmin, fn ($q) => $q->whereIn('team_id', $allowedTeamIds))
                ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
                ->latest()
                ->paginate(15);
        }

        // Build nested category tree (parents with children)
        $allCategories = ArticleCategory::with('children')
            ->whereNull('parent_id')
            ->when(! $isAdmin, fn ($q) => $q->whereIn('team_id', $allowedTeamIds))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.knowledge.article-list', [
            'articles'   => $articles,
            'categories' => $allCategories,
        ]);
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
            return Team::pluck('id')->map(fn ($id) => (int) $id)->all();
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
