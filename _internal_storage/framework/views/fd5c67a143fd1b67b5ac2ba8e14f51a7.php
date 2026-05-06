<div class="h-full mt-4" x-data="kanbanBoard()">
    <div class="flex flex-nowrap gap-4 overflow-x-auto pb-4 items-start minimal-scrollbar h-full min-h-[70vh]">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div 
                class="flex-shrink-0 w-80 bg-gray-100 rounded-lg shadow-sm flex flex-col max-h-full"
                @dragover.prevent="dragOver($event, '<?php echo e($status); ?>')"
                @drop.prevent="drop($event, '<?php echo e($status); ?>')"
                @dragenter.prevent="dragEnter($event, '<?php echo e($status); ?>')"
                @dragleave.prevent="dragLeave($event)"
                :class="{ 'bg-blue-50 ring-2 ring-blue-400': activeColumn === '<?php echo e($status); ?>' }"
            >
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="font-bold text-gray-700 uppercase tracking-wide text-xs">
                        <?php echo e(str_replace('_', ' ', $status)); ?>

                        <span class="ml-2 text-gray-400 font-normal">(<?php echo e($ticketsByStatus[$status]->count()); ?>)</span>
                    </h3>
                </div>
                
                <div class="p-3 overflow-y-auto flex-1 space-y-3 minimal-scrollbar" style="min-height: 150px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketsByStatus[$status]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, <?php echo e($ticket->id); ?>)"
                            @dragend="dragEnd()"
                            class="bg-white p-3 rounded shadow-sm border border-gray-200 cursor-move hover:border-blue-400 hover:shadow-md transition-shadow relative"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-mono text-gray-500">#<?php echo e($ticket->id); ?></span>
                                <?php
                                    $pcls = match($ticket->priority) {
                                        'critical' => 'bg-red-100 text-red-800',
                                        'high'     => 'bg-orange-100 text-orange-800',
                                        'medium'   => 'bg-yellow-100 text-yellow-800',
                                        default    => 'bg-gray-100 text-gray-600',
                                    };
                                ?>
                                <span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] uppercase font-semibold <?php echo e($pcls); ?>">
                                    <?php echo e($ticket->priority); ?>

                                </span>
                            </div>
                            <a href="<?php echo e(route('agent.tickets.show', $ticket->ulid)); ?>" class="block font-medium text-gray-900 leading-snug mb-2 hover:text-blue-600 hover:underline">
                                <?php echo e($ticket->subject); ?>

                            </a>
                            <div class="flex justify-between items-center text-xs text-gray-500">
                                <div class="truncate max-w-[120px]">
                                    <?php echo e($ticket->requester?->name ?? 'Unknown'); ?>

                                </div>
                                <div class="flex items-center gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->assignee): ?>
                                        <div class="h-5 w-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold" title="Assignee: <?php echo e($ticket->assignee->name); ?>">
                                            <?php echo e(substr($ticket->assignee->name, 0, 1)); ?>

                                        </div>
                                    <?php else: ?>
                                        <span class="text-[10px] bg-gray-200 px-1 rounded">Unassigned</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg max-w-sm">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?php echo e($message); ?></span>
        </div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php $__env->startPush('scripts'); ?>
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
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('updateTicketStatus', this.draggedTicketId, newStatus);
                    }
                }
            }))
        })
    </script>
    <?php $__env->stopPush(); ?>
    
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
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/tickets/ticket-kanban.blade.php ENDPATH**/ ?>