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
                @if($ticket->hasMedia('attachments'))
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Attachments</p>
                        <div class="mt-2 flex flex-wrap gap-3">
                            @foreach($ticket->getMedia('attachments') as $media)
                                @if(str_starts_with($media->mime_type ?? '', 'image/'))
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="block">
                                        <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" class="h-24 w-24 rounded border border-gray-200 object-cover hover:opacity-90" />
                                    </a>
                                @else
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="inline-flex items-center gap-1 rounded bg-white px-2 py-1 text-xs font-medium text-blue-600 shadow-sm border border-gray-200 hover:bg-gray-50">
                                        📎 {{ $media->name }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
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
                <form method="POST" action="{{ route($routePrefix . '.tickets.comments.add', $ticket) }}" enctype="multipart/form-data" wire:submit.prevent="addComment">
                    @csrf
                    <textarea
                        wire:model="commentBody"
                        name="comment_body"
                        rows="4"
                        placeholder="Write a reply..."
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    ></textarea>
                    @error('commentBody') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    
                    <div class="mt-3">
                        <input type="file" wire:model="attachments" name="attachments[]" multiple class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        @error('attachments.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" wire:model="isInternal" name="is_internal" value="1" @checked($isInternal) class="rounded border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            Internal note (agents only)
                        </label>
                        <button type="submit" wire:loading.attr="disabled" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Submit
                        </button>
                    </div>
                </form>
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
                <form method="POST" action="{{ route($routePrefix . '.tickets.status.save', $ticket) }}" wire:submit.prevent="updateStatus">
                    @csrf
                    @method('PATCH')
                    <select wire:model="newStatus" name="status" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                        @foreach($statuses as $s)
                            <option value="{{ $s }}">{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" wire:loading.attr="disabled" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                        Update Status
                    </button>
                </form>
            </div>

            {{-- Priority --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Priority</h3>
                <form method="POST" action="{{ route($routePrefix . '.tickets.priority.save', $ticket) }}" wire:submit.prevent="updatePriority">
                    @csrf
                    @method('PATCH')
                    <select wire:model="newPriority" name="priority" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                    <button type="submit" wire:loading.attr="disabled" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                        Update Priority
                    </button>
                </form>
            </div>

            {{-- Assignee --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Assignee</h3>
                <form method="POST" action="{{ route($routePrefix . '.tickets.assignee.save', $ticket) }}" wire:submit.prevent="updateAssignee">
                    @csrf
                    @method('PATCH')
                    <select wire:model="newAssigneeId" name="assignee_id" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" wire:loading.attr="disabled" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                        Update Assignee
                    </button>
                </form>
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
                    <form method="POST" action="{{ route($routePrefix . '.tickets.watch.toggle', $ticket) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs {{ $ticket->watchers->contains(Auth::id()) ? 'text-red-600' : 'text-blue-600' }} hover:underline">
                            {{ $ticket->watchers->contains(Auth::id()) ? 'Unwatch' : 'Watch' }}
                        </button>
                    </form>
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

            {{-- CAB Approval Panel (change tickets only) --}}
            @if($ticket->type === 'change')
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm space-y-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Change Advisory Board</h3>

                {{-- Status badge --}}
                <div>
                    @php
                        $cabStatus = match($ticket->status) {
                            'pending_approval' => ['label' => 'Awaiting Approval', 'class' => 'bg-yellow-100 text-yellow-700'],
                            'approved'         => ['label' => 'Approved', 'class' => 'bg-green-100 text-green-700'],
                            'rejected'         => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-700'],
                            'scheduled'        => ['label' => 'Scheduled', 'class' => 'bg-blue-100 text-blue-700'],
                            default            => null,
                        };
                    @endphp
                    @if($cabStatus)
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cabStatus['class'] }}">
                            {{ $cabStatus['label'] }}
                        </span>
                    @endif
                </div>

                {{-- Change details form --}}
                @if(in_array($ticket->status, ['open', 'rejected']))
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs text-indigo-700 font-medium mb-0.5">Change Type</label>
                        <select wire:model="changeType" class="w-full rounded border border-indigo-200 bg-white px-2 py-1.5 text-xs">
                            <option value="normal">Normal</option>
                            <option value="standard">Standard</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-indigo-700 font-medium mb-0.5">Risk Level</label>
                        <select wire:model="riskLevel" class="w-full rounded border border-indigo-200 bg-white px-2 py-1.5 text-xs">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-indigo-700 font-medium mb-0.5">Scheduled Date/Time</label>
                        <input type="datetime-local" wire:model="scheduledAt"
                               class="w-full rounded border border-indigo-200 bg-white px-2 py-1.5 text-xs">
                    </div>
                    <button type="button" wire:click="saveChangeDetails"
                            class="w-full rounded bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-1.5 text-xs font-medium">
                        Save Details
                    </button>
                </div>
                @else
                <div class="text-xs text-indigo-700 space-y-1">
                    <p>Type: <strong>{{ ucfirst($ticket->change_type ?? 'Normal') }}</strong></p>
                    <p>Risk: <strong>{{ ucfirst($ticket->risk_level ?? 'Low') }}</strong></p>
                    @if($ticket->scheduled_at)
                        <p>Scheduled: <strong>{{ $ticket->scheduled_at->format('d M Y H:i') }}</strong></p>
                    @endif
                </div>
                @endif

                {{-- Approvers list --}}
                <div>
                    <p class="text-xs font-medium text-indigo-700 mb-1">Approvers</p>
                    @forelse($ticket->changeApprovers as $approver)
                    <div class="flex items-center justify-between py-1 border-b border-indigo-100 last:border-0">
                        <div>
                            <span class="text-xs font-medium text-gray-800">{{ $approver->user->name }}</span>
                            <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full {{ match($approver->decision) {
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default    => 'bg-gray-100 text-gray-500',
                            } }}">{{ $approver->decision ?? 'Pending' }}</span>
                        </div>
                        @if($ticket->status === 'open')
                        <button type="button" wire:click="removeCabApprover({{ $approver->id }})"
                                class="text-xs text-red-400 hover:text-red-600">✕</button>
                        @endif
                    </div>
                    @if($approver->comment)
                        <p class="text-xs text-gray-500 italic pl-2 pb-1">{{ $approver->comment }}</p>
                    @endif
                    @empty
                    <p class="text-xs text-indigo-400 italic">No approvers assigned yet.</p>
                    @endforelse
                </div>

                {{-- Add approver search --}}
                @if(in_array($ticket->status, ['open', 'rejected']))
                <div>
                    <input type="text" wire:model.live.debounce.300ms="cabApproverSearch"
                           placeholder="Search approvers…"
                           class="w-full rounded border border-indigo-200 bg-white px-2 py-1.5 text-xs">
                    @foreach($cabApproverResults as $user)
                    <div class="flex items-center justify-between py-1 border-b border-indigo-100">
                        <span class="text-xs text-gray-700">{{ $user->name }}</span>
                        <button type="button" wire:click="addCabApprover({{ $user->id }})"
                                class="text-xs text-indigo-600 hover:underline">Add</button>
                    </div>
                    @endforeach
                </div>

                {{-- Submit for approval --}}
                @if($ticket->changeApprovers->isNotEmpty())
                <button type="button" wire:click="submitForApproval" wire:loading.attr="disabled"
                        class="w-full rounded bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 text-xs font-semibold">
                    Submit for CAB Approval
                </button>
                @endif
                @endif

                {{-- In-app approve/reject for admins when pending --}}
                @if($ticket->status === 'pending_approval' && auth()->user()?->role === 'admin')
                @php $myApproval = $ticket->changeApprovers->firstWhere('user_id', auth()->id()); @endphp
                @if($myApproval && $myApproval->isPending())
                <form method="POST" action="{{ route('change.approval.in-app', $myApproval->id) }}" class="space-y-2">
                    @csrf
                    <p class="text-xs font-medium text-indigo-700">Your Decision</p>
                    <textarea name="comment" rows="2" placeholder="Optional comment…"
                              class="w-full rounded border border-indigo-200 bg-white px-2 py-1.5 text-xs"></textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" name="decision" value="approved"
                                class="rounded bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 text-xs font-semibold">
                            ✅ Approve
                        </button>
                        <button type="submit" name="decision" value="rejected"
                                class="rounded bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 text-xs font-semibold">
                            ❌ Reject
                        </button>
                    </div>
                </form>
                @endif
                @endif
            </div>
            @endif

            {{-- Merge --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Merge Into</h3>
                <form method="POST" action="{{ route($routePrefix . '.tickets.merge', $ticket) }}" wire:submit.prevent="mergeInto">
                    @csrf
                    <input
                        wire:model="mergeTargetUlid"
                        name="merge_target_ulid"
                        type="text"
                        placeholder="Target ticket ULID"
                        class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                    />
                    @error('mergeTargetUlid') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="submit" wire:loading.attr="disabled" class="mt-2 w-full rounded bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                        onclick="return confirm('Merge this ticket? This cannot be undone.')">
                        Merge Ticket
                    </button>
                </form>
            </div>

            {{-- AI Assist --}}
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-indigo-600">✨ AI Assist</h3>

                @if ($aiLoading)
                    <p class="text-xs text-indigo-500 animate-pulse">Thinking…</p>
                @endif

                <div class="space-y-2">
                    <button type="button" wire:click="aiSummarise" wire:loading.attr="disabled" class="w-full rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                        Summarise Ticket
                    </button>
                    <button type="button" wire:click="aiDraft" wire:loading.attr="disabled" class="w-full rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                        Draft Reply
                    </button>
                    <button type="button" wire:click="aiSuggestArticles" wire:loading.attr="disabled" class="w-full rounded bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
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
                        <button type="button" wire:click="useAiDraft" class="mt-2 text-xs text-indigo-600 hover:underline">Use this draft →</button>
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
