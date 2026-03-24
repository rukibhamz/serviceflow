@extends('portal.layout')

@section('title', 'My Tickets')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Dashboard
            </a>
            <span class="text-gray-300">/</span>
            <h1 class="text-xl font-bold">My Tickets</h1>
        </div>
        <a href="{{ route('portal.tickets.create') }}"
           class="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
            New Ticket
        </a>
    </div>

    @forelse($tickets as $ticket)
        <a href="{{ route('portal.tickets.show', $ticket->ulid) }}"
           class="mb-2 flex items-center justify-between rounded border border-gray-200 bg-white px-4 py-3 hover:shadow-sm">
            <div>
                <p class="font-medium text-gray-800">{{ $ticket->subject }}</p>
                <p class="text-xs text-gray-400">{{ $ticket->created_at->format('M j, Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $ticket->priority }}</span>
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">{{ $ticket->status }}</span>
            </div>
        </a>
    @empty
        <p class="text-sm text-gray-500">You have no tickets yet.</p>
    @endforelse

    <div class="mt-4">{{ $tickets->links() }}</div>
@endsection
