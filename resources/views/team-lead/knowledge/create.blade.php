@extends('layouts.team-lead')

@section('content')
    <div class="mb-4">
        <a href="{{ route('team-lead.knowledge.index') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to KB</a>
    </div>

    <h1 class="mb-6 text-xl font-semibold text-gray-900">New Article</h1>

    <livewire:knowledge.article-editor />
@endsection

