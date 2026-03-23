<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p>A new comment has been added to ticket #<?php echo e($ticket->ulid); ?> - <?php echo e($ticket->subject); ?></p>
    <hr>
    <p><?php echo e($comment->body); ?></p>
    <hr>
    <p><a href="<?php echo e(url('/tickets/' . $ticket->ulid)); ?>">View Ticket</a></p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\emails\tickets\comment.blade.php ENDPATH**/ ?>