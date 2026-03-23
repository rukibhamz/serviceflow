<div class="space-y-4">

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="search" type="search"
               placeholder="Search assets…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56">
        <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All statuses</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>"><?php echo e($s); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <div class="ml-auto flex gap-2">
            <button wire:click="$set('showImport', true)"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Import CSV</button>
            <button wire:click="newAsset"
                    class="px-3 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">+ New Asset</button>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
        <h3 class="font-medium text-gray-700"><?php echo e($editingId ? 'Edit Asset' : 'New Asset'); ?></h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                <input wire:model="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
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
                <label class="block text-xs font-medium text-gray-600 mb-1">Type *</label>
                <input wire:model="type" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="laptop, monitor, phone…">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Serial Number</label>
                <input wire:model="serialNumber" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Asset Tag</label>
                <input wire:model="assetTag" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select wire:model="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s); ?>"><?php echo e($s); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Purchased At</label>
                <input wire:model="purchasedAt" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button wire:click="resetForm" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
            <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Save</button>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImport): ?>
    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
        <h3 class="font-medium text-gray-700">Import Assets from CSV</h3>
        <p class="text-xs text-gray-500">Required columns: <code>name, type</code>. Optional: <code>serial_number, asset_tag, status, purchased_at</code></p>
        <input wire:model="importFile" type="file" accept=".csv,.xlsx,.xls" class="text-sm">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['importFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importCreated !== null): ?>
            <p class="text-green-600 text-sm">✓ <?php echo e($importCreated); ?> asset(s) imported.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importErrors): ?>
            <div class="text-red-600 text-xs space-y-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $importErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row => $errs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p>Row <?php echo e($row); ?>: <?php echo e(implode(', ', $errs)); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="flex gap-2">
            <button wire:click="runImport" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Import</button>
            <button wire:click="$set('showImport', false)" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Close</button>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assigningId): ?>
    <div class="bg-white border border-indigo-200 rounded-xl p-5 shadow-sm space-y-3">
        <h3 class="font-medium text-gray-700">Assign Asset</h3>
        <input wire:model.live.debounce.300ms="assigneeSearch" type="search"
               placeholder="Search users…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $assigneeResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                <span class="text-sm text-gray-700"><?php echo e($user->name); ?> <span class="text-gray-400 text-xs"><?php echo e($user->email); ?></span></span>
                <button wire:click="assignTo(<?php echo e($user->id); ?>)" class="text-xs text-indigo-600 hover:underline">Assign</button>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <button wire:click="$set('assigningId', null)" class="text-xs text-gray-400 hover:underline">Cancel</button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Serial</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Assigned To</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800"><?php echo e($asset->name); ?></td>
                    <td class="px-4 py-3 text-gray-500"><?php echo e($asset->type); ?></td>
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs"><?php echo e($asset->serial_number ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full
                            <?php echo e($asset->status === 'in_use' ? 'bg-blue-100 text-blue-700' :
                               ($asset->status === 'available' ? 'bg-green-100 text-green-700' :
                               ($asset->status === 'retired' ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-700'))); ?>">
                            <?php echo e($asset->status); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($asset->assignee?->name ?? '—'); ?></td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button wire:click="openAssign(<?php echo e($asset->id); ?>)" class="text-xs text-indigo-600 hover:underline">Assign</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asset->assigned_to): ?>
                            <button wire:click="unassign(<?php echo e($asset->id); ?>)" class="text-xs text-orange-500 hover:underline">Unassign</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button wire:click="edit(<?php echo e($asset->id); ?>)" class="text-xs text-gray-500 hover:underline">Edit</button>
                        <button wire:click="delete(<?php echo e($asset->id); ?>)" wire:confirm="Delete this asset?" class="text-xs text-red-500 hover:underline">Delete</button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No assets found.</td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($assets->links()); ?></div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\livewire\asset\asset-list.blade.php ENDPATH**/ ?>