

<?php $__env->startSection('title', 'Support Portal'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="mb-6 text-2xl font-bold">Welcome, <?php echo e(auth()->user()->name); ?></h1>

    
    <div class="mb-8">
        <label class="mb-1 block text-sm font-medium text-gray-700">Search Knowledge Base</label>
        <div class="relative" x-data="{ query: '', results: [] }"
             x-init="$watch('query', async (q) => {
                 if (q.length < 2) { results = []; return; }
                 const r = await fetch('<?php echo e(route('portal.kb.search')); ?>?q=' + encodeURIComponent(q));
                 const data = await r.json();
                 results = data.results;
             })">
            <input x-model="query" type="text" placeholder="Search articles..."
                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <ul x-show="results.length > 0"
                class="absolute z-10 mt-1 w-full rounded border border-gray-200 bg-white shadow-lg">
                <template x-for="r in results" :key="r.id">
                    <li>
                        <a :href="r.url" x-text="r.title"
                           class="block px-4 py-2 text-sm hover:bg-blue-50"></a>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Your Open Tickets</h2>
        <a href="<?php echo e(route('portal.tickets.create')); ?>"
           class="rounded bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
            New Ticket
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $openTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e(route('portal.tickets.show', $ticket->ulid)); ?>"
           class="mb-2 flex items-center justify-between rounded border border-gray-200 bg-white px-4 py-3 hover:shadow-sm">
            <span class="font-medium text-gray-800"><?php echo e($ticket->subject); ?></span>
            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700"><?php echo e($ticket->status); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-sm text-gray-500">No open tickets. <a href="<?php echo e(route('portal.tickets.create')); ?>" class="text-blue-600 hover:underline">Submit one?</a></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($openTickets->isNotEmpty()): ?>
        <a href="<?php echo e(route('portal.tickets.index')); ?>" class="mt-3 inline-block text-sm text-blue-600 hover:underline">View all tickets →</a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\portal\index.blade.php ENDPATH**/ ?>