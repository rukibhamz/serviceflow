@extends('layouts.agent')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Automation Rules</h2>
            <p class="text-sm text-gray-500 mt-1">Automate ticket routing, escalations and notifications</p>
        </div>
        <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ New Rule</button>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
        <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <p class="text-gray-500 font-medium">No automation rules configured</p>
        <p class="text-sm text-gray-400 mt-1">Create rules to automatically assign, escalate or notify on ticket events.</p>
    </div>
@endsection
