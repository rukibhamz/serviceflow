<div>
    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Bulk action bar --}}
    @if(count($selected) > 0)
        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
            <span class="text-sm font-medium text-blue-800">{{ count($selected) }} selected</span>

            <div class="flex items-center gap-2">
                <select wire:model="bulkAssigneeId" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="">Assign to...</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
                <button wire:click="bulkAssign" class="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                    Assign
                </button>
            </div>

            <div class="flex items-center gap-2">
                <select wire:model="bulkStatus" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="">Set status...</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}">{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                    @endforeach
                </select>
                <button wire:click="bulkUpdateStatus" class="rounded bg-gray-700 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800">
                    Update Status
                </button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="selectAll" class="text-xs text-blue-600 hover:underline">All</button>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">ID</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Subject</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Priority</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Requester</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-gray-50 {{ in_array($ticket->id, $selected) ? 'bg-blue-50' : '' }}">
                        <td class="px-4 py-3">
                            <input
                                type="checkbox"
                                wire:click="toggleSelect({{ $ticket->id }})"
                                {{ in_array($ticket->id, $selected) ? 'checked' : '' }}
                                class="rounded"
                            />
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $ticket->id }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('agent.tickets.show', $ticket->ulid) }}" class="font-medium text-gray-900 hover:text-blue-600 hover:underline">
                                {{ $ticket->subject }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $priorityClasses = [
                                    'critical' => 'bg-red-100 text-red-800',
                                    'high'     => 'bg-orange-100 text-orange-800',
                                    'medium'   => 'bg-yellow-100 text-yellow-800',
                                    'low'      => 'bg-gray-100 text-gray-600',
                                ];
                                $pcls = $priorityClasses[$ticket->priority] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $pcls }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ ucfirst(str_replace('_', ' ', $ticket->type)) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $ticket->requester?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">No unassigned tickets in the queue.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
