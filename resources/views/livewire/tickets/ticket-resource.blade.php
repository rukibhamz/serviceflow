<div wire:poll.30s="keepAlive">
    {{-- Collision Detection Banner --}}
    @if(!empty($otherViewers))
        <div class="mb-4 flex items-center gap-2 rounded-lg bg-yellow-50 px-4 py-2 border border-yellow-200">
            <span class="flex h-2 w-2 rounded-full bg-yellow-400 animate-ping"></span>
            <p class="text-sm text-yellow-800">
                <strong>Warning:</strong> {{ implode(', ', $otherViewers) }} {{ count($otherViewers) > 1 ? 'are' : 'is' }} also viewing this ticket.
            </p>
        </div>
    @endif

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded bg-red-100 px-4 py-2 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="flex gap-6">
        {{-- Main content --}}
        <div class="flex-1 min-w-0">
            {{-- Subject & description --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h1 class="text-xl font-semibold text-gray-900">{{ $ticket->subject }}</h1>
                <p class="mt-1 text-xs text-gray-400">
                    #{{ $ticket->id }} &middot; {{ $ticket->ulid }} &middot;
                    Opened by {{ $ticket->requester?->name ?? 'Unknown' }}
                    &middot; {{ $ticket->created_at->diffForHumans() }}
                </p>
                @if($ticket->description)
                    <div class="mt-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</div>
                @endif
            </div>

            {{-- Comment thread --}}
            <div class="mt-6 space-y-4">
                @foreach($ticket->comments as $comment)
                    @if($comment->is_internal)
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                            <div class="flex items-center justify-between text-xs text-yellow-700 mb-1">
                                <span class="font-medium">{{ $comment->author?->name ?? 'System' }}</span>
                                <span>{{ $comment->created_at->diffForHumans() }} &middot; <em>Internal note</em></span>
                            </div>
                            <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $comment->body }}</p>
                            @if($comment->hasMedia('attachments'))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($comment->getMedia('attachments') as $media)
                                        <a href="{{ $media->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-white px-2 py-1 text-xs font-medium text-gray-700 shadow-sm border border-gray-200 hover:bg-gray-50">
                                            📎 {{ $media->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span class="font-medium text-gray-700">{{ $comment->author?->name ?? 'System' }}</span>
                                <span>{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $comment->body }}</p>
                            @if($comment->hasMedia('attachments'))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($comment->getMedia('attachments') as $media)
                                        <a href="{{ $media->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-white px-2 py-1 text-xs font-medium text-blue-600 shadow-sm border border-gray-200 hover:bg-gray-50">
                                            📎 {{ $media->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Comment form --}}
            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-medium text-gray-700">Add Comment</h3>
                <textarea
                    wire:model="commentBody"
                    rows="4"
                    placeholder="Write a reply..."
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                @error('commentBody') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                
                <div class="mt-3">
                    <input type="file" wire:model="attachments" multiple class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    @error('attachments.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" wire:model="isInternal" class="rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        Internal note (agents only)
                    </label>
                    <button wire:click="addComment" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Submit
                    </button>
                </div>
            </div>

            {{-- Activity timeline --}}
            <div class="mt-8">
                <h3 class="mb-3 text-sm font-semibold text-gray-600 uppercase tracking-wide">Activity</h3>
                <div class="space-y-2">
                    @foreach(\Spatie\Activitylog\Models\Activity::where('subject_type', get_class($ticket))->where('subject_id', $ticket->id)->latest()->get() as $activity)
                        <div class="flex items-start gap-2 text-xs text-gray-500">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                            <span>
                                <strong>{{ $activity->causer?->name ?? 'System' }}</strong>
                                {{ $activity->description }}
                                &middot; {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="w-72 flex-shrink-0 space-y-4">
            {{-- Status --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</h3>
                <select wire:model="newStatus" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                    @foreach($statuses as $s)
                        <option value="{{ $s }}">{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                    @endforeach
                </select>
                <button wire:click="updateStatus" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                    Update Status
                </button>
            </div>

            {{-- Priority --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Priority</h3>
                <select wire:model="newPriority" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
                <button wire:click="updatePriority" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                    Update Priority
                </button>
            </div>

            {{-- Assignee --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Assignee</h3>
                <select wire:model="newAssigneeId" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="">Unassigned</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
                <button wire:click="updateAssignee" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                    Update Assignee
                </button>
            </div>

            {{-- Team --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Team</h3>
                <p class="text-sm text-gray-700">{{ $ticket->team?->name ?? '—' }}</p>
            </div>

            {{-- Watchers --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Watchers</h3>
                    <button wire:click="toggleSubscription" class="text-xs {{ $ticket->watchers->contains(Auth::id()) ? 'text-red-600' : 'text-blue-600' }} hover:underline">
                        {{ $ticket->watchers->contains(Auth::id()) ? 'Unwatch' : 'Watch' }}
                    </button>
                </div>
                @if($ticket->watchers->isNotEmpty())
                    <div class="flex flex-wrap gap-1">
                        @foreach($ticket->watchers as $watcher)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600" title="{{ $watcher->name }}">
                                {{ collect(explode(' ', $watcher->name))->map(fn($n) => $n[0])->take(2)->join('') }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic">No watchers</p>
                @endif
            </div>


            {{-- SLA --}}
            @if($ticket->slaTimers->isNotEmpty())
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">SLA</h3>
                    @foreach($ticket->slaTimers as $timer)
                        <div class="text-xs text-gray-600 space-y-1">
                            @if($timer->first_response_at)
                                <p>First response: <span class="font-medium">{{ $timer->first_response_at->diffForHumans() }}</span></p>
                            @else
                                <p class="text-orange-600">Awaiting first response</p>
                            @endif
                            @if($timer->breached)
                                <p class="font-semibold text-red-600">⚠ SLA Breached</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Merge --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Merge Into</h3>
                <input
                    wire:model="mergeTargetUlid"
                    type="text"
                    placeholder="Target ticket ULID"
                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                />
                @error('mergeTargetUlid') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <button wire:click="mergeInto" class="mt-2 w-full rounded bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                    onclick="return confirm('Merge this ticket? This cannot be undone.')">
                    Merge Ticket
                </button>
            </div>

            {{-- AI Assist --}}
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-indigo-600">✨ AI Assist</h3>

                @if ($aiLoading)
                    <p class="text-xs text-indigo-500 animate-pulse">Thinking…</p>
                @endif

                <div class="space-y-2">
                    <button wire:click="aiSummarise" class="w-full rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                        Summarise Ticket
                    </button>
                    <button wire:click="aiDraft" class="w-full rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                        Draft Reply
                    </button>
                    <button wire:click="aiSuggestArticles" class="w-full rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                        Suggest KB Articles
                    </button>
                </div>

                @if ($aiSummary)
                    <div class="mt-3 rounded bg-white border border-indigo-200 p-3 text-xs text-gray-700">
                        <p class="font-semibold text-indigo-600 mb-1">Summary</p>
                        {{ $aiSummary }}
                    </div>
                @endif

                @if ($aiDraftReply)
                    <div class="mt-3 rounded bg-white border border-indigo-200 p-3 text-xs text-gray-700">
                        <p class="font-semibold text-indigo-600 mb-1">Draft Reply</p>
                        <p class="whitespace-pre-wrap">{{ $aiDraftReply }}</p>
                        <button wire:click="useAiDraft" class="mt-2 text-xs text-indigo-600 hover:underline">Use this draft →</button>
                    </div>
                @endif

                @if (!empty($aiSuggestions))
                    <div class="mt-3 rounded bg-white border border-indigo-200 p-3 text-xs text-gray-700">
                        <p class="font-semibold text-indigo-600 mb-1">Suggested Articles</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($aiSuggestions as $suggestion)
                                <li>{{ $suggestion }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
