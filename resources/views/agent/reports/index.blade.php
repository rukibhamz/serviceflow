@extends('layouts.agent')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Reports</h2>
        <p class="text-sm text-gray-500 mt-1">Analyse team performance and ticket trends</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach(['Ticket Volume', 'SLA Compliance', 'Agent Performance', 'CSAT Trends', 'Resolution Time', 'Backlog Analysis'] as $report)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                <p class="font-semibold text-gray-800 text-sm">{{ $report }}</p>
                <p class="text-xs text-gray-400 mt-1">Coming soon</p>
            </div>
        @endforeach
    </div>
@endsection
