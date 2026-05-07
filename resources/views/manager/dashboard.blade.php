@extends('layouts.manager')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manager Dashboard</h1>
        <p class="text-sm text-gray-500">Organization oversight and performance snapshot.</p>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Teams</p><p class="text-2xl font-bold">{{ $stats['teams'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Team Leads</p><p class="text-2xl font-bold">{{ $stats['team_leads'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Agents</p><p class="text-2xl font-bold">{{ $stats['agents'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Open Tickets</p><p class="text-2xl font-bold">{{ $stats['open_tickets'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Changes</p><p class="text-2xl font-bold">{{ $stats['changes'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Problems</p><p class="text-2xl font-bold">{{ $stats['problems'] }}</p></div>
    </div>
    <div class="rounded-lg border bg-white">
        <div class="border-b px-4 py-3 text-sm font-semibold">Recent Tickets</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr><th class="px-4 py-2 text-left">Subject</th><th class="px-4 py-2 text-left">Team</th><th class="px-4 py-2 text-left">Status</th><th class="px-4 py-2 text-left"></th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="px-4 py-2">{{ $ticket->subject }}</td>
                            <td class="px-4 py-2">{{ $ticket->team?->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                            <td class="px-4 py-2 text-right"><a class="text-blue-600 hover:underline" href="{{ route('manager.tickets.show', $ticket->ulid) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No recent tickets.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

