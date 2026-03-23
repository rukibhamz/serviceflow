@extends('portal.layout')

@section('title', $ticket->subject)

@section('content')
    <a href="{{ route('portal.tickets.index') }}" class="mb-4 inline-block text-sm text-blue-600 hover:underline">← My Tickets</a>

    <div class="rounded border border-gray-200 bg-white p-6">
        <div class="mb-4 flex items-start justify-between">
            <h1 class="text-xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">{{ $ticket->status }}</span>
        </div>

        <div class="mb-4 flex gap-4 text-xs text-gray-500">
            <span>Priority: <strong>{{ $ticket->priority }}</strong></span>
            <span>Type: <strong>{{ $ticket->type }}</strong></span>
            <span>Submitted: <strong>{{ $ticket->created_at->format('M j, Y') }}</strong></span>
        </div>

        @if($ticket->description)
            <div class="mb-6 rounded bg-gray-50 p-4 text-sm text-gray-700">
                {{ $ticket->description }}
            </div>
        @endif

        {{-- Comments --}}
        <h2 class="mb-3 text-sm font-semibold text-gray-700">Updates</h2>
        @forelse($ticket->comments()->where('is_internal', false)->latest()->get() as $comment)
            <div class="mb-3 rounded border border-gray-100 bg-gray-50 p-3 text-sm">
                <p class="mb-1 text-xs text-gray-400">{{ $comment->created_at->format('M j, Y H:i') }}</p>
                <p class="text-gray-800">{{ $comment->body }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400">No updates yet.</p>
        @endforelse
    </div>
@endsection
