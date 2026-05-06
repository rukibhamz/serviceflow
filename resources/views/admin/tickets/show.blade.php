@extends('layouts.admin')

@section('content')
    @php
        $backRoute = match($ticket->type) {
            'problem' => 'admin.problems.index',
            'change' => 'admin.changes.index',
            default => 'admin.tickets.index',
        };
        $backLabel = match($ticket->type) {
            'problem' => 'Back to problems',
            'change' => 'Back to change requests',
            default => 'Back to tickets',
        };
    @endphp
    <div class="mb-4">
        <a href="{{ route($backRoute) }}" class="text-sm text-blue-600 hover:underline">&larr; {{ $backLabel }}</a>
    </div>
    <livewire:tickets.ticket-resource :ticket="$ticket" />
@endsection

