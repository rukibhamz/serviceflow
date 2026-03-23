<?php

namespace App\Livewire\Knowledge;

use App\Models\ArticleCategory;
use App\Models\KnowledgeArticle;
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
        if ($this->search !== '') {
            $searchResults = $searchService->search($this->search, 50);

            // Apply category filter on top of search results
            if ($this->categoryId !== '') {
                $searchResults = $searchResults->filter(
                    fn ($a) => (string) $a->category_id === $this->categoryId
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
                ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
                ->latest()
                ->paginate(15);
        }

        // Build nested category tree (parents with children)
        $allCategories = ArticleCategory::with('children')->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get();

        return view('livewire.knowledge.article-list', [
            'articles'   => $articles,
            'categories' => $allCategories,
        ]);
    }
}
