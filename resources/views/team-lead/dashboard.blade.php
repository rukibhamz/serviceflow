@extends('layouts.team-lead')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Team Lead Dashboard</h1>
        <p class="text-sm text-gray-500">Manage and oversee your assigned teams.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('team-lead.service-catalogue.index') }}" class="btn-ds primary">Service Catalogue</a>
        <a href="{{ route('team-lead.service-catalogue.create') }}" class="btn-ds ghost">+ New Catalogue Item</a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Teams</p><p class="text-2xl font-bold">{{ $stats['teams'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Members</p><p class="text-2xl font-bold">{{ $stats['members'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Open Tickets</p><p class="text-2xl font-bold">{{ $stats['open_tickets'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Change Requests</p><p class="text-2xl font-bold">{{ $stats['changes'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Problems</p><p class="text-2xl font-bold">{{ $stats['problems'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs text-gray-500">Active Catalogue Items</p><p class="text-2xl font-bold">{{ $stats['catalogue_active'] }}</p></div>
    </div>

    <div class="rounded-lg border bg-white">
        <div class="border-b px-4 py-3 text-sm font-semibold">Recent Team Tickets</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-2 text-left">Subject</th>
                        <th class="px-4 py-2 text-left">Type</th>
                        <th class="px-4 py-2 text-left">Team</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="px-4 py-2">{{ $ticket->subject }}</td>
                            <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $ticket->type)) }}</td>
                            <td class="px-4 py-2">{{ $ticket->team?->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</td>
                            <td class="px-4 py-2 text-right"><a class="text-blue-600 hover:underline" href="{{ route('team-lead.tickets.show', $ticket->ulid) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No tickets yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="rounded-lg border bg-white">
        <div class="border-b px-4 py-3 text-sm font-semibold">Recent Catalogue Items</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr><th class="px-4 py-2 text-left">Name</th><th class="px-4 py-2 text-left">Type</th><th class="px-4 py-2 text-left">Team</th><th class="px-4 py-2 text-left">Status</th><th class="px-4 py-2 text-left"></th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($recentCatalogue as $item)
                        <tr>
                            <td class="px-4 py-2">{{ $item->name }}</td>
                            <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</td>
                            <td class="px-4 py-2">{{ $item->team?->name ?? '—' }}</td>
                            <td class="px-4 py-2">{{ $item->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="px-4 py-2 text-right"><a class="text-blue-600 hover:underline" href="{{ route('team-lead.service-catalogue.edit', $item) }}">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No catalogue items yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

