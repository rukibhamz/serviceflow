<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeArticle;
use Illuminate\Support\Collection;
use TeamTNT\TNTSearch\TNTSearch;

class ArticleSearchService
{
    private TNTSearch $tnt;

    public function __construct()
    {
        $storage = config('tnt.storage', storage_path('app/tnt'));

        if (! is_dir($storage)) {
            mkdir($storage, 0755, true);
        }

        $this->tnt = new TNTSearch();
        $this->tnt->loadConfig([
            'storage'  => $storage,
            'driver'   => config('tnt.driver', 'filesystem'),
            'location' => $storage,
        ]);
    }

    /**
     * Full-text search over published KnowledgeArticle records, returning ranked results.
     */
    public function search(string $query, int $limit = 10): Collection
    {
        $indexName = config('tnt.index', 'knowledge_articles.index');

        try {
            $this->tnt->selectIndex($indexName);
            $results = $this->tnt->search($query, $limit);
            $ids     = $results['ids'] ?? [];

            if (empty($ids)) {
                return collect();
            }

            // Preserve TNTSearch ranking order, filter to published only
            return KnowledgeArticle::whereIn('id', $ids)
                ->where('status', 'published')
                ->get()
                ->sortBy(fn ($article) => array_search($article->id, $ids))
                ->values();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Index a published article into TNTSearch. Only published articles are indexed.
     */
    public function indexArticle(KnowledgeArticle $article): void
    {
        if ($article->status !== 'published') {
            return;
        }

        $indexName = config('tnt.index', 'knowledge_articles.index');

        $document = [
            'id'    => $article->id,
            'title' => $article->title,
            'body'  => strip_tags($article->body),
        ];

        try {
            $this->tnt->selectIndex($indexName);
            $index = $this->tnt->getIndex();
            $index->update($article->id, $document);
        } catch (\Exception $e) {
            // Index may not exist yet — create it and insert
            $this->createIndex();
            $this->tnt->selectIndex($indexName);
            $index = $this->tnt->getIndex();
            $index->insert($document);
        }
    }

    /**
     * Remove an article from the TNTSearch index.
     */
    public function removeFromIndex(KnowledgeArticle $article): void
    {
        $indexName = config('tnt.index', 'knowledge_articles.index');

        try {
            $this->tnt->selectIndex($indexName);
            $index = $this->tnt->getIndex();
            $index->delete($article->id);
        } catch (\Exception $e) {
            // Index doesn't exist or article not indexed — nothing to do
        }
    }

    private function createIndex(): void
    {
        $indexName = config('tnt.index', 'knowledge_articles.index');
        $indexer   = $this->tnt->createIndex($indexName);
        $indexer->setPrimaryKey('id');
        $indexer->setStopWords([]);
        // Do not call run() — we insert documents individually via insert/updateWithDocument.
        // run() is for bulk-loading from a database or filesystem source, and requires
        // additional config keys (e.g. 'extension') that are not relevant here.
    }
}
