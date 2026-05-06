<div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selected) > 0): ?>
        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
            <span class="text-sm font-medium text-blue-800"><?php echo e(count($selected)); ?> selected</span>

            <div class="flex items-center gap-2">
                <select wire:model="bulkAssigneeId" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="">Assign to...</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($agent->id); ?>"><?php echo e($agent->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <button wire:click="bulkAssign" class="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                    Assign
                </button>
            </div>

            <div class="flex items-center gap-2">
                <select wire:model="bulkStatus" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <option value="">Set status...</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>"><?php echo e(str_replace('_', ' ', ucfirst($s))); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <button wire:click="bulkUpdateStatus" class="rounded bg-gray-700 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-800">
                    Update Status
                </button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 <?php echo e(in_array($ticket->id, $selected) ? 'bg-blue-50' : ''); ?>">
                        <td class="px-4 py-3">
                            <input
                                type="checkbox"
                                wire:click="toggleSelect(<?php echo e($ticket->id); ?>)"
                                <?php echo e(in_array($ticket->id, $selected) ? 'checked' : ''); ?>

                                class="rounded"
                            />
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">#<?php echo e($ticket->id); ?></td>
                        <td class="px-4 py-3">
                            <a href="<?php echo e(route('agent.tickets.show', $ticket->ulid)); ?>" class="font-medium text-gray-900 hover:text-blue-600 hover:underline">
                                <?php echo e($ticket->subject); ?>

                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <?php
                                $priorityClasses = [
                                    'critical' => 'bg-red-100 text-red-800',
                                    'high'     => 'bg-orange-100 text-orange-800',
                                    'medium'   => 'bg-yellow-100 text-yellow-800',
                                    'low'      => 'bg-gray-100 text-gray-600',
                                ];
                                $pcls = $priorityClasses[$ticket->priority] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($pcls); ?>">
                                <?php echo e(ucfirst($ticket->priority)); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e(ucfirst(str_replace('_', ' ', $ticket->type))); ?></td>
                        <td class="px-4 py-3 text-gray-700"><?php echo e($ticket->requester?->name ?? '—'); ?></td>
                        <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($ticket->created_at->diffForHumans()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">No unassigned tickets in the queue.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="mt-4">
        <?php echo e($tickets->links()); ?>

    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/tickets/triage-queue.blade.php ENDPATH**/ ?>