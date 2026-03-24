@extends('layouts.agent')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Knowledge Base</h1>
        <a href="{{ route('agent.knowledge.create') }}"
           class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            New Article
        </a>
    </div>

    <livewire:knowledge.article-list />
@endsection
