<div wire:poll.30s="keepAlive">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($otherViewers)): ?>
        <div class="mb-4 flex items-center gap-2 rounded-lg bg-yellow-50 px-4 py-2 border border-yellow-200">
            <span class="flex h-2 w-2 rounded-full bg-yellow-400 animate-ping"></span>
            <p class="text-sm text-yellow-800">
                <strong>Warning:</strong> <?php echo e(implode(', ', $otherViewers)); ?> <?php echo e(count($otherViewers) > 1 ? 'are' : 'is'); ?> also viewing this ticket.
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="mb-4 rounded bg-red-100 px-4 py-2 text-sm text-red-800"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex gap-6">
        
        <div class="flex-1 min-w-0">
            
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h1 class="text-xl font-semibold text-gray-900"><?php echo e($ticket->subject); ?></h1>
                <p class="mt-1 text-xs text-gray-400">
                    #<?php echo e($ticket->id); ?> &middot; <?php echo e($ticket->ulid); ?> &middot;
                    Opened by <?php echo e($ticket->requester?->name ?? 'Unknown'); ?>

                    &middot; <?php echo e($ticket->created_at->diffForHumans()); ?>

                </p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->description): ?>
                    <div class="mt-4 text-sm text-gray-700 whitespace-pre-wrap"><?php echo e($ticket->description); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->hasMedia('attachments')): ?>
                    <div class="mt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Attachments</p>
                        <div class="mt-2 flex flex-wrap gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticket->getMedia('attachments'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($media->mime_type ?? '', 'image/')): ?>
                                    <a href="<?php echo e($media->getUrl()); ?>" target="_blank" class="block">
                                        <img src="<?php echo e($media->getUrl()); ?>" alt="<?php echo e($media->name); ?>" class="h-24 w-24 rounded border border-gray-200 object-cover hover:opacity-90" />
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo e($media->getUrl()); ?>" target="_blank" class="inline-flex items-center gap-1 rounded bg-white px-2 py-1 text-xs font-medium text-blue-600 shadow-sm border border-gray-200 hover:bg-gray-50">
                                        📎 <?php echo e($media->name); ?>

                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="mt-6 space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticket->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comment->is_internal): ?>
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                            <div class="flex items-center justify-between text-xs text-yellow-700 mb-1">
                                <span class="font-medium"><?php echo e($comment->author?->name ?? 'System'); ?></span>
                                <span><?php echo e($comment->created_at->diffForHumans()); ?> &middot; <em>Internal note</em></span>
                            </div>
                            <p class="text-sm text-gray-800 whitespace-pre-wrap"><?php echo e($comment->body); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comment->hasMedia('attachments')): ?>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $comment->getMedia('attachments'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e($media->getUrl()); ?>" target="_blank" class="inline-flex items-center gap-1 rounded bg-white px-2 py-1 text-xs font-medium text-gray-700 shadow-sm border border-gray-200 hover:bg-gray-50">
                                            📎 <?php echo e($media->name); ?>

                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span class="font-medium text-gray-700"><?php echo e($comment->author?->name ?? 'System'); ?></span>
                                <span><?php echo e($comment->created_at->diffForHumans()); ?></span>
                            </div>
                            <p class="text-sm text-gray-800 whitespace-pre-wrap"><?php echo e($comment->body); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comment->hasMedia('attachments')): ?>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $comment->getMedia('attachments'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e($media->getUrl()); ?>" target="_blank" class="inline-flex items-center gap-1 rounded bg-white px-2 py-1 text-xs font-medium text-blue-600 shadow-sm border border-gray-200 hover:bg-gray-50">
                                            📎 <?php echo e($media->name); ?>

                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-medium text-gray-700">Add Comment</h3>
                <textarea
                    wire:model="commentBody"
                    rows="4"
                    placeholder="Write a reply..."
                    class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['commentBody'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <div class="mt-3">
                    <input type="file" wire:model="attachments" multiple class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

            
            <div class="mt-8">
                <h3 class="mb-3 text-sm font-semibold text-gray-600 uppercase tracking-wide">Activity</h3>
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \Spatie\Activitylog\Models\Activity::where('subject_type', get_class($ticket))->where('subject_id', $ticket->id)->latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-2 text-xs text-gray-500">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-gray-300 flex-shrink-0"></span>
                            <span>
                                <strong><?php echo e($activity->causer?->name ?? 'System'); ?></strong>
                                <?php echo e($activity->description); ?>

                                &middot; <?php echo e($activity->created_at->diffForHumans()); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="w-72 flex-shrink-0 space-y-4">
            
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</h3>
                <select wire:model="newStatus" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>"><?php echo e(str_replace('_', ' ', ucfirst($s))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <button wire:click="updateStatus" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                    Update Status
                </button>
            </div>

            
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

            
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Assignee</h3>
                <select wire:model="newAssigneeId" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="">Unassigned</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($agent->id); ?>"><?php echo e($agent->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <button wire:click="updateAssignee" class="mt-2 w-full rounded bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                    Update Assignee
                </button>
            </div>

            
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Team</h3>
                <p class="text-sm text-gray-700"><?php echo e($ticket->team?->name ?? '—'); ?></p>
            </div>

            
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Watchers</h3>
                    <button wire:click="toggleSubscription" class="text-xs <?php echo e($ticket->watchers->contains(Auth::id()) ? 'text-red-600' : 'text-blue-600'); ?> hover:underline">
                        <?php echo e($ticket->watchers->contains(Auth::id()) ? 'Unwatch' : 'Watch'); ?>

                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->watchers->isNotEmpty()): ?>
                    <div class="flex flex-wrap gap-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticket->watchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $watcher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600" title="<?php echo e($watcher->name); ?>">
                                <?php echo e(collect(explode(' ', $watcher->name))->map(fn($n) => $n[0])->take(2)->join('')); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-gray-400 italic">No watchers</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>


            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->slaTimers->isNotEmpty()): ?>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">SLA</h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticket->slaTimers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-xs text-gray-600 space-y-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($timer->first_response_at): ?>
                                <p>First response: <span class="font-medium"><?php echo e($timer->first_response_at->diffForHumans()); ?></span></p>
                            <?php else: ?>
                                <p class="text-orange-600">Awaiting first response</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($timer->breached): ?>
                                <p class="font-semibold text-red-600">⚠ SLA Breached</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Merge Into</h3>
                <input
                    wire:model="mergeTargetUlid"
                    type="text"
                    placeholder="Target ticket ULID"
                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm"
                />
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['mergeTargetUlid'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button wire:click="mergeInto" class="mt-2 w-full rounded bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                    onclick="return confirm('Merge this ticket? This cannot be undone.')">
                    Merge Ticket
                </button>
            </div>

            
            <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-indigo-600">✨ AI Assist</h3>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aiLoading): ?>
                    <p class="text-xs text-indigo-500 animate-pulse">Thinking…</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aiSummary): ?>
                    <div class="mt-3 rounded bg-white border border-indigo-200 p-3 text-xs text-gray-700">
                        <p class="font-semibold text-indigo-600 mb-1">Summary</p>
                        <?php echo e($aiSummary); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aiDraftReply): ?>
                    <div class="mt-3 rounded bg-white border border-indigo-200 p-3 text-xs text-gray-700">
                        <p class="font-semibold text-indigo-600 mb-1">Draft Reply</p>
                        <p class="whitespace-pre-wrap"><?php echo e($aiDraftReply); ?></p>
                        <button wire:click="useAiDraft" class="mt-2 text-xs text-indigo-600 hover:underline">Use this draft →</button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($aiSuggestions)): ?>
                    <div class="mt-3 rounded bg-white border border-indigo-200 p-3 text-xs text-gray-700">
                        <p class="font-semibold text-indigo-600 mb-1">Suggested Articles</p>
                        <ul class="list-disc list-inside space-y-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $aiSuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($suggestion); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/tickets/ticket-resource.blade.php ENDPATH**/ ?>