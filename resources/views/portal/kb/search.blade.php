@extends('portal.layout')

@section('title', 'Knowledge Base Search')

@section('content')
    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Dashboard
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-sm text-gray-500">Knowledge Base</span>
    </div>

    <h1 class="mb-4 text-xl font-bold text-gray-900">Knowledge Base</h1>

    <form method="GET" action="{{ route('portal.kb.search') }}" class="mb-6">
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search articles..."
                   class="flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" autofocus />
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Search</button>
        </div>
    </form>

    @if($query !== '')
        <p class="mb-4 text-sm text-gray-500">
            {{ $articles->count() }} result(s) for "<strong>{{ $query }}</strong>"
        </p>
    @endif

    @if($articles->isNotEmpty())
        <div class="space-y-3">
            @foreach($articles as $article)
                <a href="{{ route('agent.knowledge.show', $article->slug) }}"
                   class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="font-semibold text-gray-900">{{ $article->title }}</h3>
                    @if($article->category)
                        <span class="mt-1 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">
                            {{ $article->category->name }}
                        </span>
                    @endif
                    <p class="mt-1 text-sm text-gray-500">{{ Str::limit(strip_tags($article->body), 150) }}</p>
                </a>
            @endforeach
        </div>
    @elseif($query !== '')
        <div class="rounded-lg border border-gray-200 bg-white p-10 text-center">
            <p class="text-gray-500">No articles found for "{{ $query }}".</p>
            <p class="mt-1 text-sm text-gray-400">Try different keywords or <a href="{{ route('portal.tickets.create') }}" class="text-blue-600 hover:underline">submit a ticket</a>.</p>
        </div>
    @else
        <div class="rounded-lg border border-gray-200 bg-white p-10 text-center">
            <p class="text-gray-400 text-sm">Enter a search term above to find help articles.</p>
        </div>
    @endif
@endsection
