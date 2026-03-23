<div>
    
    <div class="flex items-center justify-between mb-4">
        <button wire:click="previousMonth" class="px-3 py-1 rounded border text-sm hover:bg-gray-100">&larr;</button>
        <h2 class="text-lg font-semibold text-gray-700">
            <?php echo e(\Carbon\Carbon::create($year, $month, 1)->format('F Y')); ?>

        </h2>
        <button wire:click="nextMonth" class="px-3 py-1 rounded border text-sm hover:bg-gray-100">&rarr;</button>
    </div>

    
    <div class="grid grid-cols-7 text-center text-xs font-medium text-gray-500 mb-1">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div><?php echo e($dow); ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded overflow-hidden">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < $startDow; $i++): ?>
            <div class="bg-gray-50 min-h-[80px]"></div>
        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($day = 1; $day <= $daysInMonth; $day++): ?>
            <?php
                $dateKey = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                $dayChanges = $changes[$dateKey] ?? collect();
            ?>
            <div class="bg-white min-h-[80px] p-1">
                <div class="text-xs font-medium text-gray-400 mb-1"><?php echo e($day); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dayChanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('agent.tickets.show', $change->ulid)); ?>"
                       class="block text-xs truncate rounded px-1 py-0.5 mb-0.5
                              <?php echo e($change->risk_level === 'high' ? 'bg-red-100 text-red-700' : ($change->risk_level === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')); ?>"
                       title="<?php echo e($change->subject); ?>">
                        <?php echo e($change->subject); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\livewire\change\change-calendar.blade.php ENDPATH**/ ?>