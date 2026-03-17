@extends('layouts.agent')

@section('content')
    <div class="mb-4">
        <a href="{{ route('knowledge.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Back to Knowledge Base</a>
    </div>

    <h1 class="mb-4 text-xl font-semibold text-gray-900">New Article</h1>

    <livewire:knowledge.article-editor />
@endsection
