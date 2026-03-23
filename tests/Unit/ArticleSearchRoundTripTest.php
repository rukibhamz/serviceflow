<?php

/**
 * Property 9: Knowledge Article Search Round-Trip
 * Validates: Requirements 5.3
 *
 * Index a generated set of articles; for each article assert a search on a unique
 * term from its title returns that article in results. Also assert that draft
 * articles are NOT returned in search results.
 */

use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\Knowledge\ArticleSearchService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => Event::fake());

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Create a fresh ArticleSearchService pointed at a unique temp directory so
 * each test iteration starts with a clean, isolated TNT index.
 */
function makeFreshSearchService(): ArticleSearchService
{
    $tmpDir = sys_get_temp_dir() . '/tnt_test_' . bin2hex(random_bytes(8));
    config(['tnt.storage' => $tmpDir, 'tnt.index' => 'knowledge_articles.index']);

    return new ArticleSearchService();
}

/**
 * Create a published KnowledgeArticle with a unique hex token embedded in its title.
 * Returns [$article, $token].
 */
function makePublishedArticle(User $author): array
{
    $token = bin2hex(random_bytes(6)); // 12-char hex, guaranteed unique
    $title = 'Article ' . $token . ' ' . Str::random(8);
    $slug  = Str::slug($title) . '-' . bin2hex(random_bytes(4));

    $article = KnowledgeArticle::create([
        'title'     => $title,
        'slug'      => $slug,
        'body'      => 'Body content for ' . $token,
        'status'    => 'published',
        'author_id' => $author->id,
    ]);

    return [$article, $token];
}

/**
 * Create a draft KnowledgeArticle with a unique hex token in its title.
 * Returns [$article, $token].
 */
function makeDraftArticle(User $author): array
{
    $token = bin2hex(random_bytes(6));
    $title = 'Draft ' . $token . ' ' . Str::random(8);
    $slug  = Str::slug($title) . '-' . bin2hex(random_bytes(4));

    $article = KnowledgeArticle::create([
        'title'     => $title,
        'slug'      => $slug,
        'body'      => 'Draft body for ' . $token,
        'status'    => 'draft',
        'author_id' => $author->id,
    ]);

    return [$article, $token];
}

// ── Unit tests ────────────────────────────────────────────────────────────────

test('indexArticle and search returns a published article by its unique title token', function () {
    $author  = User::factory()->create();
    $service = makeFreshSearchService();

    [$article, $token] = makePublishedArticle($author);
    $service->indexArticle($article);

    $results = $service->search($token);

    expect($results->pluck('id'))->toContain($article->id);
});

test('draft articles are not returned even when indexed', function () {
    $author  = User::factory()->create();
    $service = makeFreshSearchService();

    // Index a published article first so the index exists
    [$published, $pubToken] = makePublishedArticle($author);
    $service->indexArticle($published);

    // Attempt to index a draft (indexArticle should skip it)
    [$draft, $draftToken] = makeDraftArticle($author);
    $service->indexArticle($draft);

    $results = $service->search($draftToken);

    expect($results->pluck('id'))->not->toContain($draft->id);
});

test('search returns empty collection when no articles are indexed', function () {
    $service = makeFreshSearchService();

    $results = $service->search('nonexistenttoken' . bin2hex(random_bytes(4)));

    expect($results)->toBeEmpty();
});

test('each article in a batch is findable by its unique token', function () {
    $author  = User::factory()->create();
    $service = makeFreshSearchService();

    $count    = rand(3, 6);
    $articles = [];

    for ($i = 0; $i < $count; $i++) {
        [$article, $token] = makePublishedArticle($author);
        $service->indexArticle($article);
        $articles[] = ['article' => $article, 'token' => $token];
    }

    foreach ($articles as ['article' => $article, 'token' => $token]) {
        $results = $service->search($token);
        $ids = $results->pluck('id')->toArray();
        expect(in_array($article->id, $ids))->toBeTrue(
            "Article {$article->id} should appear when searching for its unique token '{$token}'"
        );
    }
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 9: Knowledge Article Search Round-Trip
 *
 * For 100 iterations:
 *   - Generate 3–10 published articles, each with a unique hex token in its title
 *   - Index all articles via ArticleSearchService::indexArticle
 *   - For each article, search using its unique token and assert it appears in results
 *   - Generate 1–3 draft articles, index them (indexArticle skips drafts), and
 *     assert their tokens do NOT appear in search results
 */
it('finds every indexed published article by its unique title token and excludes drafts', function () {
    $author  = User::factory()->create();
    $service = makeFreshSearchService();

    // ── 1. Generate and index 3–10 published articles ────────────────────────
    $count    = rand(3, 10);
    $articles = [];

    for ($i = 0; $i < $count; $i++) {
        [$article, $token] = makePublishedArticle($author);
        $service->indexArticle($article);
        $articles[] = ['article' => $article, 'token' => $token];
    }

    // ── 2. Assert each published article is findable by its unique token ─────
    foreach ($articles as ['article' => $article, 'token' => $token]) {
        $results = $service->search($token);
        $ids = $results->pluck('id')->toArray();

        expect(in_array($article->id, $ids))->toBeTrue(
            "Published article {$article->id} should appear in results for token '{$token}'"
        );
    }

    // ── 3. Generate 1–3 draft articles and assert they are NOT returned ──────
    $draftCount = rand(1, 3);

    for ($i = 0; $i < $draftCount; $i++) {
        [$draft, $draftToken] = makeDraftArticle($author);
        $service->indexArticle($draft); // should be a no-op for drafts

        $draftResults = $service->search($draftToken);
        $draftIds = $draftResults->pluck('id')->toArray();

        expect(in_array($draft->id, $draftIds))->toBeFalse(
            "Draft article {$draft->id} must NOT appear in search results"
        );
    }
})->repeat(100);
