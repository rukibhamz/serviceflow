@extends('layouts.agent')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Triage Queue</h2>
        <p class="mt-1 text-sm text-gray-500">Unassigned open tickets awaiting assignment.</p>
    </div>
    <livewire:tickets.ticket-triage-queue />
@endsection
