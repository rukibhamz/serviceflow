@extends('layouts.agent')

@section('content')
    <div class="mb-4">
        <a href="{{ route('agent.knowledge.index') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to KB</a>
    </div>

    <article class="mx-auto max-w-3xl">
        <header class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $article->title }}</h1>
            <div class="mt-2 flex items-center gap-3 text-sm text-gray-500">
                @if($article->category)
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">
                        {{ $article->category->name }}
                    </span>
                @endif
                <span>{{ $article->created_at->format('M j, Y') }}</span>
                <span>{{ $article->view_count }} views</span>
            </div>
        </header>

        <div class="prose max-w-none text-gray-800">
            {!! nl2br(e($article->body)) !!}
        </div>

        {{-- Vote buttons --}}
        <div class="mt-8 border-t pt-6">
            <p class="mb-3 text-sm font-medium text-gray-700">Was this article helpful?</p>
            <div class="flex gap-3">
                <form method="POST" action="{{ route('knowledge.vote', $article->slug) }}">
                    @csrf
                    <input type="hidden" name="helpful" value="1" />
                    <button type="submit"
                            class="rounded border border-green-300 bg-green-50 px-4 py-2 text-sm text-green-700 hover:bg-green-100">
                        👍 Yes ({{ $article->helpful_votes }})
                    </button>
                </form>
                <form method="POST" action="{{ route('knowledge.vote', $article->slug) }}">
                    @csrf
                    <input type="hidden" name="helpful" value="0" />
                    <button type="submit"
                            class="rounded border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                        👎 No ({{ $article->unhelpful_votes }})
                    </button>
                </form>
            </div>
        </div>
    </article>
@endsection
