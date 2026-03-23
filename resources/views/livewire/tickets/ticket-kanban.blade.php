<div class="h-full mt-4" x-data="kanbanBoard()">
    <div class="flex flex-nowrap gap-4 overflow-x-auto pb-4 items-start minimal-scrollbar h-full min-h-[70vh]">
        @foreach($statuses as $status)
            <div 
                class="flex-shrink-0 w-80 bg-gray-100 rounded-lg shadow-sm flex flex-col max-h-full"
                @dragover.prevent="dragOver($event, '{{ $status }}')"
                @drop.prevent="drop($event, '{{ $status }}')"
                @dragenter.prevent="dragEnter($event, '{{ $status }}')"
                @dragleave.prevent="dragLeave($event)"
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
                            <a href="{{ route('agent.tickets.show', $ticket->ulid) }}" class="block font-medium text-gray-900 leading-snug mb-2 hover:text-blue-600 hover:underline">
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

    {{-- Require Alpine.js to be globally accessible if not already setup appropriately --}}
    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kanbanBoard', () => ({
                draggedTicketId: null,
                activeColumn: null,
                
                dragStart(e, ticketId) {
                    this.draggedTicketId = ticketId;
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', ticketId);
                    setTimeout(() => e.target.classList.add('opacity-50'), 0);
                },
                dragEnd(e) {
                    this.draggedTicketId = null;
                    this.activeColumn = null;
                    document.querySelectorAll('.opacity-50').forEach(el => el.classList.remove('opacity-50'));
                },
                dragOver(e, status) {
                    // PrevenDefault is handled by @dragover.prevent
                },
                dragEnter(e, status) {
                    this.activeColumn = status;
                },
                dragLeave(e) {
                    this.activeColumn = null;
                },
                drop(e, newStatus) {
                    this.activeColumn = null;
                    if (this.draggedTicketId) {
                        @this.call('updateTicketStatus', this.draggedTicketId, newStatus);
                    }
                }
            }))
        })
    </script>
    @endpush
    
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
