@extends('portal.layout')

@section('title', $ticket->subject)

@section('content')
    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('portal.tickets.index') }}" class="text-sm text-blue-600 hover:underline">My Tickets</a>
    </div>

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

        @if($ticket->watchers->isNotEmpty())
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <span class="text-xs text-gray-500">Tagged:</span>
                @foreach($ticket->watchers as $watcher)
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">
                        <span class="flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-white" style="font-size:9px">{{ strtoupper(substr($watcher->name, 0, 2)) }}</span>
                        {{ $watcher->name }}
                    </span>
                @endforeach
            </div>
        @endif

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
