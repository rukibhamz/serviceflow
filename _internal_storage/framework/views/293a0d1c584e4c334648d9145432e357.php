<div x-data="{ query: '' }" class="pad-ds">
    <div class="mb-8 text-center max-w-2xl mx-auto py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">How can we help you today?</h1>
        <div class="relative">
            <input 
                type="text" 
                x-model="query"
                @input.debounce.300ms="fetchResults"
                placeholder="Search knowledge base articles..." 
                class="form-input-ds py-4 pl-12 shadow-sm text-lg"
            >
            <div class="absolute left-4 top-1/2 -translate-y-1/2 opacity-40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
        <div class="mt-4 flex gap-2 justify-center">
            <a href="<?php echo e(route('portal.tickets.create')); ?>" class="btn-ds primary">Submit a Request</a>
            <a href="<?php echo e(route('portal.catalogue.index')); ?>" class="btn-ds">View Service Catalogue</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="stats-ds">
                <div class="stat-card">
                    <div class="stat-label">Your Open Requests</div>
                    <div class="stat-val text-brand"><?php echo e($stats['open'] ?? 0); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Resolved Tickets</div>
                    <div class="stat-val text-success"><?php echo e($stats['resolved'] ?? 0); ?></div>
                </div>
            </div>

            
            <div class="card-ds">
                <div class="card-hdr">
                    <div class="card-title">Recent Tickets</div>
                    <a href="<?php echo e(route('portal.tickets.index')); ?>" class="text-xs text-brand hover:underline">View all</a>
                </div>
                <div class="card-body p-0">
                    <table class="table-ds">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $openTickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="cursor-pointer" onclick="window.location='<?php echo e(route('portal.tickets.show', $ticket->ulid)); ?>'">
                                    <td>
                                        <div class="font-medium text-gray-900"><?php echo e($ticket->title); ?></div>
                                        <div class="text-xs text-gray-400">#<?php echo e(strtoupper(substr($ticket->ulid, -6))); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-ds <?php echo e($ticket->status); ?>">
                                            <?php echo e($ticket->status); ?>

                                        </span>
                                    </td>
                                    <td class="text-gray-500 whitespace-nowrap">
                                        <?php echo e($ticket->updated_at->diffForHumans()); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-6 text-gray-400">You have no open tickets.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="space-y-6">
            <div class="card-ds">
                <div class="card-hdr">
                    <div class="card-title text-brand">Quick Help</div>
                    <a href="<?php echo e(route('portal.kb.search')); ?>" class="text-xs text-brand hover:underline">Browse all</a>
                </div>
                <div class="card-body">
                    <ul class="space-y-3">
                        <li>
                            <a href="<?php echo e(route('portal.kb.search')); ?>?q=password+reset" class="flex flex-col gap-1 hover:bg-gray-50 p-2 rounded transition">
                                <span class="text-sm font-medium">How to reset your password?</span>
                                <span class="text-xs text-gray-400">Search knowledge base</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('portal.kb.search')); ?>?q=hardware+request" class="flex flex-col gap-1 hover:bg-gray-50 p-2 rounded transition">
                                <span class="text-sm font-medium">Requesting new hardware</span>
                                <span class="text-xs text-gray-400">Search knowledge base</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo e(route('portal.kb.search')); ?>?q=VPN+remote+access" class="flex flex-col gap-1 hover:bg-gray-50 p-2 rounded transition">
                                <span class="text-sm font-medium">Remote Access VPN Guide</span>
                                <span class="text-xs text-gray-400">Search knowledge base</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/portal/user-dashboard.blade.php ENDPATH**/ ?>