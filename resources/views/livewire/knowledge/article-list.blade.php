<div>
    {{-- Search & Filter --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search articles..."
            class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <select wire:model.live="categoryId" class="rounded border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Article Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($articles as $article)
            <a href="{{ route('knowledge.show', $article->slug) }}"
               class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
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
