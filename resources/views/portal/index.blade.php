@extends('portal.layout')

@section('title', 'Support Portal')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Welcome, {{ auth()->user()->name }}</h1>

    {{-- KB Search --}}
    <div class="mb-8">
        <label class="mb-1 block text-sm font-medium text-gray-700">Search Knowledge Base</label>
        <div class="relative" x-data="{ query: '', results: [] }"
             x-init="$watch('query', async (q) => {
                 if (q.length < 2) { results = []; return; }
                 const r = await fetch('{{ route('portal.kb.search') }}?q=' + encodeURIComponent(q));
                 const data = await r.json();
                 results = data.results;
             })">
            <input x-model="query" type="text" placeholder="Search articles..."
                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <ul x-show="results.length > 0"
                class="absolute z-10 mt-1 w-full rounded border border-gray-200 bg-white shadow-lg">
                <template x-for="r in results" :key="r.id">
                    <li>
                        <a :href="r.url" x-text="r.title"
                           class="block px-4 py-2 text-sm hover:bg-blue-50"></a>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    {{-- Open Tickets --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Your Open Tickets</h2>
        <a href="{{ route('portal.tickets.create') }}"
           class="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
            New Ticket
        </a>
    </div>

    @forelse($openTickets as $ticket)
        <a href="{{ route('portal.tickets.show', $ticket->ulid) }}"
           class="mb-2 flex items-center justify-between rounded border border-gray-200 bg-white px-4 py-3 hover:shadow-sm">
            <span class="font-medium text-gray-800">{{ $ticket->subject }}</span>
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">{{ $ticket->status }}</span>
        </a>
    @empty
        <p class="text-sm text-gray-500">No open tickets. <a href="{{ route('portal.tickets.create') }}" class="text-blue-600 hover:underline">Submit one?</a></p>
    @endforelse

    @if($openTickets->isNotEmpty())
        <a href="{{ route('portal.tickets.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">View all tickets →</a>
    @endif
@endsection
