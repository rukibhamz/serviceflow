<?php

namespace App\Livewire\Knowledge;

use App\Models\ArticleCategory;
use App\Models\KnowledgeArticle;
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

    public function render()
    {
        $articles = KnowledgeArticle::query()
            ->with('category')
            ->where('status', 'published')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                  ->orWhere('body', 'like', '%'.$this->search.'%');
            }))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->latest()
            ->paginate(15);

        $categories = ArticleCategory::orderBy('name')->get();

        return view('livewire.knowledge.article-list', compact('articles', 'categories'));
    }
}
