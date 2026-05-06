@extends('layouts.admin')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Knowledge Base</h1>
        <a href="{{ route('admin.knowledge.create') }}"
           class="btn-ds primary">
            New Article
        </a>
    </div>

    <livewire:knowledge.article-list />
@endsection
