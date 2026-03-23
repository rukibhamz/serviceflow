<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p><strong>SLA Breach Alert</strong></p>
    <p>Ticket #<?php echo e($timer->ticket->ulid); ?> - <?php echo e($timer->ticket->subject); ?> has breached its SLA.</p>
    <p><strong>Breach Type:</strong> <?php echo e($timer->type); ?></p>
    <p><strong>Status:</strong> <?php echo e($timer->ticket->status); ?></p>
    <p><strong>Priority:</strong> <?php echo e($timer->ticket->priority); ?></p>
    <p><a href="<?php echo e(url('/tickets/' . $timer->ticket->ulid)); ?>">View Ticket</a></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\emails\tickets\sla-breached.blade.php ENDPATH**/ ?>