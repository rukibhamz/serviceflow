<div class="space-y-4">
    <?php
        $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'agent';
    ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="search" type="search"
               placeholder="Search problems…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56">
        <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
        </select>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($routePrefix === 'agent'): ?>
            <div class="ml-auto">
                <a href="<?php echo e(route($routePrefix . '.tickets.create')); ?>?type=problem" class="btn-ds primary">+ New Problem</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rootCauseId): ?>
    <div class="bg-white border border-orange-200 rounded-xl p-5 shadow-sm space-y-4">
        <h3 class="font-medium text-gray-700">Root Cause Analysis</h3>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Root Cause</label>
            <textarea wire:model="rootCause" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                      placeholder="Describe the root cause…"></textarea>
        </div>
        <div>
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer mb-2">
                <input wire:model="markKnownError" type="checkbox" class="rounded border-gray-300">
                Mark as Known Error (KEDB)
            </label>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($markKnownError): ?>
            <textarea wire:model="workaround" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                      placeholder="Describe the workaround…"></textarea>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button wire:click="$set('rootCauseId', null)" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
            <button wire:click="saveRootCause" class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded-lg">Save</button>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($linkingId): ?>
    <div class="bg-white border border-indigo-200 rounded-xl p-5 shadow-sm space-y-3">
        <h3 class="font-medium text-gray-700">Link Incidents</h3>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($linkedIncidents->isNotEmpty()): ?>
        <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 mb-1">Linked incidents</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $linkedIncidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                <span class="text-sm text-gray-700"><?php echo e($inc->subject); ?></span>
                <button wire:click="unlinkIncident(<?php echo e($inc->id); ?>)" class="text-xs text-red-500 hover:underline">Unlink</button>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <input wire:model.live.debounce.300ms="incidentSearch" type="search"
               placeholder="Search unlinked incidents…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $incidentResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex items-center justify-between py-1 border-b border-gray-100">
            <span class="text-sm text-gray-700"><?php echo e($inc->subject); ?></span>
            <button wire:click="linkIncident(<?php echo e($inc->id); ?>)" class="text-xs text-indigo-600 hover:underline">Link</button>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button wire:click="$set('linkingId', null)" class="text-xs text-gray-400 hover:underline">Close</button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">Assignee</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Incidents</th>
                    <th class="px-4 py-3 text-center">Known Error</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $problems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $problem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $incCount   = \App\Models\Ticket::where('problem_id', $problem->id)->count();
                    $knownError = $problem->custom_fields['known_error'] ?? false;
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="<?php echo e(route($routePrefix . '.tickets.show', $problem->ulid)); ?>" class="font-medium text-brand hover:underline">
                            <?php echo e($problem->subject); ?>

                        </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($problem->custom_fields['root_cause'] ?? null): ?>
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs"><?php echo e($problem->custom_fields['root_cause']); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?php echo e($problem->assignee?->name ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full
                            <?php echo e(match($problem->status) {
                                'open'        => 'bg-blue-100 text-blue-700',
                                'in_progress' => 'bg-purple-100 text-purple-700',
                                'resolved'    => 'bg-green-100 text-green-700',
                                'closed'      => 'bg-gray-100 text-gray-500',
                                default       => 'bg-gray-100 text-gray-600',
                            }); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $problem->status))); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600"><?php echo e($incCount); ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($knownError): ?>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">KEDB</span>
                        <?php else: ?>
                            <span class="text-gray-300">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openRootCause(<?php echo e($problem->id); ?>)" class="text-xs text-orange-500 hover:underline">RCA</button>
                        <button wire:click="openLinkPanel(<?php echo e($problem->id); ?>)" class="text-xs text-indigo-600 hover:underline">Incidents</button>
                        <a href="<?php echo e(route($routePrefix . '.tickets.show', $problem->ulid)); ?>" class="text-xs text-gray-500 hover:underline">View</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No problem records yet.</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($problems->links()); ?></div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/problem/problem-list.blade.php ENDPATH**/ ?>