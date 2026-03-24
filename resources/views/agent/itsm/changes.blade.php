@extends('layouts.agent')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Change Management</h2>
            <p class="text-sm text-gray-500 mt-1">Track and manage change requests</p>
        </div>
        <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ New Change</button>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
        <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <p class="text-gray-500 font-medium">No change requests yet</p>
        <p class="text-sm text-gray-400 mt-1">Change requests will appear here once created.</p>
    </div>
@endsection
