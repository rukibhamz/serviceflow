

<?php $__env->startSection('page-header'); ?>
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Change Management</div>
            <div class="page-sub">Track and approve infrastructure and software changes</div>
        </div>
        <a href="<?php echo e(route('admin.tickets.create')); ?>?type=change" class="btn-ds primary">+ New Change Request</a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $total    = \App\Models\Ticket::where('type','change')->count();
    $pending  = \App\Models\Ticket::where('type','change')->where('status','pending_approval')->count();
    $approved = \App\Models\Ticket::where('type','change')->where('status','approved')->count();
    $scheduled= \App\Models\Ticket::where('type','change')->where('status','scheduled')->count();
?>
<div class="space-y-4">
    <div class="stats-ds mb-2">
        <div class="stat-card"><div class="stat-label">Total Changes</div><div class="stat-val text-brand"><?php echo e($total); ?></div></div>
        <div class="stat-card"><div class="stat-label">Pending Approval</div><div class="stat-val text-yellow-500"><?php echo e($pending); ?></div></div>
        <div class="stat-card"><div class="stat-label">Approved</div><div class="stat-val text-green-600"><?php echo e($approved); ?></div></div>
        <div class="stat-card"><div class="stat-label">Scheduled</div><div class="stat-val text-blue-500"><?php echo e($scheduled); ?></div></div>
    </div>
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">CAB Calendar</div></div>
        <div class="card-body"><?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('change.change-calendar', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3382137597-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?></div>
    </div>
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Change Requests</div></div>
        <div class="card-body p-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Requester</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Scheduled</th>
                        <th class="px-4 py-3 text-left">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = \App\Models\Ticket::where('type','change')->with('requester')->latest()->take(20)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="<?php echo e(route('admin.tickets.show', $change->ulid)); ?>" class="font-medium text-brand hover:underline"><?php echo e($change->subject); ?></a></td>
                        <td class="px-4 py-3 text-gray-500"><?php echo e($change->requester?->name ?? '—'); ?></td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full <?php echo e(match($change->status) { 'pending_approval'=>'bg-yellow-100 text-yellow-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','scheduled'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-purple-100 text-purple-700',default=>'bg-gray-100 text-gray-600' }); ?>"><?php echo e(str_replace('_',' ',ucfirst($change->status))); ?></span></td>
                        <td class="px-4 py-3 text-gray-400 text-xs"><?php echo e($change->scheduled_at ? \Carbon\Carbon::parse($change->scheduled_at)->format('d M Y H:i') : '—'); ?></td>
                        <td class="px-4 py-3 text-gray-400 text-xs"><?php echo e($change->created_at->format('d M Y')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No change requests yet.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/admin/itsm/changes.blade.php ENDPATH**/ ?>