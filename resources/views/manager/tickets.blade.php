@extends('layouts.manager')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold text-gray-900">Tickets Oversight</h1>
    <div class="rounded-lg border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr><th class="px-4 py-2 text-left">ID</th><th class="px-4 py-2 text-left">Subject</th><th class="px-4 py-2 text-left">Type</th><th class="px-4 py-2 text-left">Status</th><th class="px-4 py-2 text-left">Team</th><th class="px-4 py-2 text-left"></th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($tickets as $ticket)
                    <tr>
                        <td class="px-4 py-2 text-xs text-gray-500">#{{ $ticket->id }}</td>
                        <td class="px-4 py-2">{{ $ticket->subject }}</td>
                        <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $ticket->type)) }}</td>
                        <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                        <td class="px-4 py-2">{{ $ticket->team?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right"><a class="text-blue-600 hover:underline" href="{{ route('manager.tickets.show', $ticket->ulid) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $tickets->links() }}</div>
</div>
@endsection

