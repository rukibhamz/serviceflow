<?php

namespace App\Observers;

use App\Models\KnowledgeArticle;
use App\Services\Knowledge\ArticleSearchService;

class KnowledgeArticleObserver
{
    public function __construct(private ArticleSearchService $searchService) {}

    public function saved(KnowledgeArticle $article): void
    {
        // Only index published articles; non-published are skipped inside indexArticle
        $this->searchService->indexArticle($article);
    }

    public function deleted(KnowledgeArticle $article): void
    {
        $this->searchService->removeFromIndex($article);
    }
}
