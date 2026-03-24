@extends('layouts.agent')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Problem Management</h2>
            <p class="text-sm text-gray-500 mt-1">Identify and resolve root causes</p>
        </div>
        <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ New Problem</button>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
        <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-gray-500 font-medium">No problems logged</p>
        <p class="text-sm text-gray-400 mt-1">Problem records will appear here once created.</p>
    </div>
@endsection
