<div class="flex gap-6">
    {{-- Category Tree Sidebar --}}
    <aside class="w-48 flex-shrink-0">
        <h2 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Categories</h2>
        <ul class="space-y-1 text-sm">
            <li>
                <button
                    wire:click="selectCategory('')"
                    class="w-full rounded px-2 py-1 text-left hover:bg-gray-100 {{ $categoryId === '' ? 'bg-blue-50 font-semibold text-blue-700' : 'text-gray-700' }}"
                >
                    All
                </button>
            </li>
            @foreach($categories as $parent)
                <li>
                    <button
                        wire:click="selectCategory('{{ $parent->id }}')"
                        class="w-full rounded px-2 py-1 text-left hover:bg-gray-100 {{ $categoryId === (string)$parent->id ? 'bg-blue-50 font-semibold text-blue-700' : 'text-gray-700' }}"
                    >
                        {{ $parent->name }}
                    </button>
                    @if($parent->children->isNotEmpty())
                        <ul class="ml-3 mt-0.5 space-y-0.5">
                            @foreach($parent->children as $child)
                                <li>
                                    <button
                                        wire:click="selectCategory('{{ $child->id }}')"
                                        class="w-full rounded px-2 py-1 text-left text-xs hover:bg-gray-100 {{ $categoryId === (string)$child->id ? 'bg-blue-50 font-semibold text-blue-700' : 'text-gray-600' }}"
                                    >
                                        {{ $child->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 min-w-0">
        {{-- Search Bar --}}
        <div class="mb-4">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search articles..."
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        {{-- Article Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($articles as $article)
                <a href="{{ route('knowledge.show', $article->slug) }}"
                   class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                    <h3 class="mb-1 font-semibold text-gray-900 line-clamp-2">{{ $article->title }}</h3>
                    @if($article->category)
                        <span class="mb-2 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">
                            {{ $article->category->name }}
                        </span>
                    @endif
                    <p class="text-sm text-gray-500 line-clamp-3">
                        {{ Str::limit(strip_tags($article->body), 120) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">{{ $article->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <div class="col-span-3 py-12 text-center text-gray-400">No articles found.</div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $articles->links() }}
        </div>
    </div>
</div>
