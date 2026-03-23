<div class="space-y-6">

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Automation Rules</h2>
        <button wire:click="newAutomation"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            + New Automation
        </button>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-5 shadow-sm">
        <h3 class="font-medium text-gray-700"><?php echo e($editingId ? 'Edit Automation' : 'New Automation'); ?></h3>

        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                <input wire:model="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Auto-assign critical tickets">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Trigger Event</label>
                <select wire:model="triggerEvent" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">— select trigger —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $triggers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trigger): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($trigger); ?>"><?php echo e($trigger); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['triggerEvent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-xs font-medium text-gray-600">Conditions</span>
                <select wire:model="condOperator" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="AND">ALL (AND)</option>
                    <option value="OR">ANY (OR)</option>
                </select>
                <button wire:click="addCondition" class="text-xs text-indigo-600 hover:underline">+ Add condition</button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $conditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cond): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-2 mb-2">
                <select wire:model="conditions.<?php echo e($i); ?>.field" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="priority">priority</option>
                    <option value="status">status</option>
                    <option value="type">type</option>
                    <option value="subject">subject</option>
                    <option value="assignee_id">assignee_id</option>
                </select>
                <select wire:model="conditions.<?php echo e($i); ?>.op" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="equals">equals</option>
                    <option value="not_equals">not equals</option>
                    <option value="contains">contains</option>
                    <option value="not_contains">not contains</option>
                    <option value="is_null">is null</option>
                    <option value="is_not_null">is not null</option>
                </select>
                <input wire:model="conditions.<?php echo e($i); ?>.value" type="text"
                       class="border border-gray-300 rounded px-2 py-1 text-xs w-32" placeholder="value">
                <button wire:click="removeCondition(<?php echo e($i); ?>)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-xs font-medium text-gray-600">Actions</span>
                <button wire:click="addAction" class="text-xs text-indigo-600 hover:underline">+ Add action</button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-start gap-2 mb-2">
                <select wire:model="actions.<?php echo e($i); ?>.type" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="assign_ticket">Assign ticket</option>
                    <option value="change_status">Change status</option>
                    <option value="add_comment">Add comment</option>
                    <option value="send_notification">Send notification</option>
                    <option value="trigger_webhook">Trigger webhook</option>
                </select>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($action['type'] === 'add_comment'): ?>
                    <input wire:model="actions.<?php echo e($i); ?>.body" type="text"
                           class="border border-gray-300 rounded px-2 py-1 text-xs flex-1" placeholder="Comment body">
                <?php elseif($action['type'] === 'change_status'): ?>
                    <input wire:model="actions.<?php echo e($i); ?>.status" type="text"
                           class="border border-gray-300 rounded px-2 py-1 text-xs w-32" placeholder="new status">
                <?php elseif($action['type'] === 'assign_ticket'): ?>
                    <input wire:model="actions.<?php echo e($i); ?>.assignee_id" type="number"
                           class="border border-gray-300 rounded px-2 py-1 text-xs w-24" placeholder="user ID">
                <?php elseif($action['type'] === 'trigger_webhook'): ?>
                    <input wire:model="actions.<?php echo e($i); ?>.url" type="url"
                           class="border border-gray-300 rounded px-2 py-1 text-xs flex-1" placeholder="https://...">
                <?php elseif($action['type'] === 'send_notification'): ?>
                    <input wire:model="actions.<?php echo e($i); ?>.user_id" type="number"
                           class="border border-gray-300 rounded px-2 py-1 text-xs w-24" placeholder="user ID">
                    <input wire:model="actions.<?php echo e($i); ?>.message" type="text"
                           class="border border-gray-300 rounded px-2 py-1 text-xs flex-1" placeholder="message">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <button wire:click="removeAction(<?php echo e($i); ?>)" class="text-red-400 hover:text-red-600 text-xs mt-1">✕</button>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input wire:model="isActive" type="checkbox" class="rounded border-gray-300">
                Active
            </label>
            <div class="flex gap-2">
                <button wire:click="cancelForm" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Save</button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Trigger</th>
                    <th class="px-4 py-3 text-center">Runs</th>
                    <th class="px-4 py-3 text-center">Active</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $automations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $automation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($automation->name); ?></td>
                    <td class="px-4 py-3 text-gray-500"><?php echo e($automation->trigger_event); ?></td>
                    <td class="px-4 py-3 text-center text-gray-500"><?php echo e($automation->run_count); ?></td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleActive(<?php echo e($automation->id); ?>)"
                                class="text-xs px-2 py-0.5 rounded-full <?php echo e($automation->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                            <?php echo e($automation->is_active ? 'On' : 'Off'); ?>

                        </button>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="edit(<?php echo e($automation->id); ?>)" class="text-indigo-600 hover:underline text-xs">Edit</button>
                        <button wire:click="delete(<?php echo e($automation->id); ?>)"
                                wire:confirm="Delete this automation?"
                                class="text-red-500 hover:underline text-xs">Delete</button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No automations yet.</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($automations->links()); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\livewire\automation\automation-builder.blade.php ENDPATH**/ ?>