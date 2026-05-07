@php $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'agent'; @endphp
<div class="h-full mt-4" x-data="{
    draggedTicketId: null,
    activeColumn: null,
    busy: false,
    statusUrlTemplate: @js(route($routePrefix . '.tickets.status.update', ['ticket' => '__TICKET__'])),
    csrf: @js(csrf_token()),
    dragStart(e, ticketId) {
        this.draggedTicketId = ticketId;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(ticketId));
        setTimeout(() => e.currentTarget.classList.add('opacity-50'), 0);
    },
    dragEnd() {
        this.draggedTicketId = null;
        this.activeColumn = null;
        document.querySelectorAll('.opacity-50').forEach(el => el.classList.remove('opacity-50'));
    },
    dragOver() {},
    dragEnter(status) {
        this.activeColumn = status;
    },
    dragLeave() {
        this.activeColumn = null;
    },
    async drop(e, newStatus) {
        this.activeColumn = null;
        const droppedId = this.draggedTicketId ?? Number(e.dataTransfer.getData('text/plain'));
        if (!droppedId || this.busy) return;
        this.busy = true;
        try {
            const url = this.statusUrlTemplate.replace('__TICKET__', String(droppedId));
            const res = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: newStatus })
            });
            if (!res.ok) {
                const body = await res.text();
                throw new Error(body || ('Status update failed with HTTP ' + res.status));
            }
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert('Unable to update ticket status. Please try again.');
        }
        this.busy = false;
        this.dragEnd();
    }
}">
    <div class="flex flex-nowrap gap-4 overflow-x-auto pb-4 items-start minimal-scrollbar h-full min-h-[70vh]">
        @foreach($statuses as $status)
            <div 
                class="flex-shrink-0 w-80 bg-gray-100 rounded-lg shadow-sm flex flex-col max-h-full"
                @dragover.prevent="dragOver()"
                @drop.prevent="drop($event, '{{ $status }}')"
                @dragenter.prevent="dragEnter('{{ $status }}')"
                @dragleave.prevent="dragLeave()"
                :class="{ 'bg-blue-50 ring-2 ring-blue-400': activeColumn === '{{ $status }}' }"
            >
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="font-bold text-gray-700 uppercase tracking-wide text-xs">
                        {{ str_replace('_', ' ', $status) }}
                        <span class="ml-2 text-gray-400 font-normal">({{ $ticketsByStatus[$status]->count() }})</span>
                    </h3>
                </div>
                
                <div class="p-3 overflow-y-auto flex-1 space-y-3 minimal-scrollbar" style="min-height: 150px;">
                    @foreach($ticketsByStatus[$status] as $ticket)
                        <div 
                            wire:key="kanban-ticket-{{ $ticket->id }}"
                            draggable="true"
                            @dragstart="dragStart($event, {{ $ticket->id }})"
                            @dragend="dragEnd()"
                            class="bg-white p-3 rounded shadow-sm border border-gray-200 cursor-move hover:border-blue-400 hover:shadow-md transition-shadow relative"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-mono text-gray-500">#{{ $ticket->id }}</span>
                                @php
                                    $pcls = match($ticket->priority) {
                                        'critical' => 'bg-red-100 text-red-800',
                                        'high'     => 'bg-orange-100 text-orange-800',
                                        'medium'   => 'bg-yellow-100 text-yellow-800',
                                        default    => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] uppercase font-semibold {{ $pcls }}">
                                    {{ $ticket->priority }}
                                </span>
                            </div>
                            @php $ticketShowRoute = request()->routeIs('admin.*') ? 'admin.tickets.show' : 'agent.tickets.show'; @endphp
                            <a href="{{ route($ticketShowRoute, $ticket->ulid) }}" class="block font-medium text-gray-900 leading-snug mb-2 hover:text-blue-600 hover:underline">
                                {{ $ticket->subject }}
                            </a>
                            <div class="flex justify-between items-center text-xs text-gray-500">
                                <div class="truncate max-w-[120px]">
                                    {{ $ticket->requester?->name ?? 'Unknown' }}
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($ticket->assignee)
                                        <div class="h-5 w-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold" title="Assignee: {{ $ticket->assignee->name }}">
                                            {{ substr($ticket->assignee->name, 0, 1) }}
                                        </div>
                                    @else
                                        <span class="text-[10px] bg-gray-200 px-1 rounded">Unassigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @error('status')
        <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg max-w-sm">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @enderror

    <style>
        .minimal-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .minimal-scrollbar::-webkit-scrollbar-track {
            background: transparent; 
        }
        .minimal-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1; 
            border-radius: 20px;
        }
    </style>
</div>
