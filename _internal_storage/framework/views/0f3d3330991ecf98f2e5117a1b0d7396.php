<div>
    <?php $ticketShowRoute = request()->routeIs('admin.*') ? 'admin.tickets.show' : 'agent.tickets.show'; ?>
    
    <div class="mb-4 flex flex-wrap gap-3">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Search tickets..."
            class="rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <select wire:model.live="statusFilter" class="rounded border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="pending">Pending</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
        </select>

        <select wire:model.live="priorityFilter" class="rounded border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
    </div>

    
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">
                        <button wire:click="sortBy('id')" class="flex items-center gap-1 hover:text-gray-900">
                            ID
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'id'): ?> <span><?php echo e($sortDir === 'asc' ? '↑' : '↓'); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">
                        <button wire:click="sortBy('subject')" class="flex items-center gap-1 hover:text-gray-900">
                            Subject
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'subject'): ?> <span><?php echo e($sortDir === 'asc' ? '↑' : '↓'); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">
                        <button wire:click="sortBy('priority')" class="flex items-center gap-1 hover:text-gray-900">
                            Priority
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'priority'): ?> <span><?php echo e($sortDir === 'asc' ? '↑' : '↓'); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Assignee</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Team</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">
                        <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-gray-900">
                            Created
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortBy === 'created_at'): ?> <span><?php echo e($sortDir === 'asc' ? '↑' : '↓'); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">
                            <a href="<?php echo e(route($ticketShowRoute, $ticket->ulid)); ?>" class="hover:underline">
                                #<?php echo e($ticket->id); ?>

                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <a href="<?php echo e(route($ticketShowRoute, $ticket->ulid)); ?>" class="font-medium text-gray-900 hover:text-blue-600 hover:underline">
                                <?php echo e($ticket->subject); ?>

                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <?php
                                $statusClasses = [
                                    'open'        => 'bg-blue-100 text-blue-800',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'pending'     => 'bg-orange-100 text-orange-800',
                                    'resolved'    => 'bg-green-100 text-green-800',
                                    'closed'      => 'bg-gray-100 text-gray-600',
                                ];
                                $cls = $statusClasses[$ticket->status] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($cls); ?>">
                                <?php echo e(str_replace('_', ' ', ucfirst($ticket->status))); ?>

                            </span>
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
                        <td class="px-4 py-3 text-gray-700"><?php echo e($ticket->assignee?->name ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-700"><?php echo e($ticket->team?->name ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($ticket->created_at->diffForHumans()); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">No tickets found.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="mt-4">
        <?php echo e($tickets->links()); ?>

    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/tickets/ticket-list.blade.php ENDPATH**/ ?>