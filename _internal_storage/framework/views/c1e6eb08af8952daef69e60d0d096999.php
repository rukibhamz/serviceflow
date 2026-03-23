<!DOCTYPE html>
<html>
<head><meta charset="UTF-8" /></head>
<body style="font-family: sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 24px;">
    <h2 style="margin-bottom: 8px;">How did we do?</h2>
    <p>Your ticket <strong>[<?php echo e($ticket->ulid); ?>] <?php echo e($ticket->subject); ?></strong> has been resolved.</p>
    <p>We'd love to hear your feedback. Please rate your experience:</p>

    <div style="margin: 24px 0; display: flex; gap: 8px;">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1, 2, 3, 4, 5]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(url('/portal/csat/' . $survey->token . '/rate/' . $rating)); ?>"
               style="display: inline-block; padding: 10px 16px; background: #2563eb; color: #fff;
                      text-decoration: none; border-radius: 6px; font-size: 16px;">
                <?php echo e($rating); ?> ★
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <p style="font-size: 12px; color: #888;">
        This link is unique to your ticket. You can update your rating at any time.
    </p>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\emails\csat\survey.blade.php ENDPATH**/ ?>