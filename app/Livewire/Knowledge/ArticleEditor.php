<?php

namespace App\Livewire\Knowledge;

use App\Models\ArticleCategory;
use App\Models\KnowledgeArticle;
use App\Services\Knowledge\ArticleService;
use Livewire\Component;

class ArticleEditor extends Component
{
    public ?KnowledgeArticle $article = null;

    public string $title = '';
    public string $body = '';
    public string $categoryId = '';
    public string $status = 'draft';

    public function mount(?KnowledgeArticle $article = null): void
    {
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
        $categories = ArticleCategory::orderBy('name')->get();

        return view('livewire.knowledge.article-editor', compact('categories'));
    }
}
