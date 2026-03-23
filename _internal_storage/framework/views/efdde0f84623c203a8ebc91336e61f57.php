<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p>A new ticket has been created: #<?php echo e($ticket->ulid); ?> - <?php echo e($ticket->subject); ?></p>
    <p><strong>Status:</strong> <?php echo e($ticket->status); ?></p>
    <p><strong>Priority:</strong> <?php echo e($ticket->priority); ?></p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->description): ?>
        <p><strong>Description:</strong></p>
        <p><?php echo e($ticket->description); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\emails\tickets\created.blade.php ENDPATH**/ ?>