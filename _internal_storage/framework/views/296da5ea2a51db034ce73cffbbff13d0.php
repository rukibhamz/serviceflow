

<?php $__env->startSection('content'); ?>
    <div class="mb-4">
        <a href="<?php echo e(route('knowledge.index')); ?>" class="text-sm text-blue-600 hover:underline">&larr; Back to Knowledge Base</a>
    </div>

    <article class="mx-auto max-w-3xl">
        <header class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900"><?php echo e($article->title); ?></h1>
            <div class="mt-2 flex items-center gap-3 text-sm text-gray-500">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($article->category): ?>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">
                        <?php echo e($article->category->name); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span><?php echo e($article->created_at->format('M j, Y')); ?></span>
                <span><?php echo e($article->view_count); ?> views</span>
            </div>
        </header>

        <div class="prose max-w-none text-gray-800">
            <?php echo nl2br(e($article->body)); ?>

        </div>

        
        <div class="mt-8 border-t pt-6">
            <p class="mb-3 text-sm font-medium text-gray-700">Was this article helpful?</p>
            <div class="flex gap-3">
                <form method="POST" action="<?php echo e(route('knowledge.vote', $article->slug)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="helpful" value="1" />
                    <button type="submit"
                            class="rounded border border-green-300 bg-green-50 px-4 py-2 text-sm text-green-700 hover:bg-green-100">
                        👍 Yes (<?php echo e($article->helpful_votes); ?>)
                    </button>
                </form>
                <form method="POST" action="<?php echo e(route('knowledge.vote', $article->slug)); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="helpful" value="0" />
                    <button type="submit"
                            class="rounded border border-red-300 bg-red-50 px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                        👎 No (<?php echo e($article->unhelpful_votes); ?>)
                    </button>
                </form>
            </div>
        </div>
    </article>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.agent', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views\agent\knowledge\show.blade.php ENDPATH**/ ?>