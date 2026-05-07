@extends('layouts.team-lead')

@section('content')
    <div class="mb-4">
        <a href="{{ route('team-lead.knowledge.show', $article->slug) }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Article</a>
    </div>

    <h1 class="mb-6 text-xl font-semibold text-gray-900">Edit Article</h1>

    <livewire:knowledge.article-editor :article="$article" />
@endsection

