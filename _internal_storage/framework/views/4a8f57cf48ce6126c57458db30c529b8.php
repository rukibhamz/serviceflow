<div class="flex gap-6">
    
    <aside class="w-48 flex-shrink-0">
        <h2 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Categories</h2>
        <ul class="space-y-1 text-sm">
            <li>
                <button
                    wire:click="selectCategory('')"
                    class="w-full rounded px-2 py-1 text-left hover:bg-gray-100 <?php echo e($categoryId === '' ? 'bg-blue-50 font-semibold text-blue-700' : 'text-gray-700'); ?>"
                >
                    All
                </button>
            </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <button
                        wire:click="selectCategory('<?php echo e($parent->id); ?>')"
                        class="w-full rounded px-2 py-1 text-left hover:bg-gray-100 <?php echo e($categoryId === (string)$parent->id ? 'bg-blue-50 font-semibold text-blue-700' : 'text-gray-700'); ?>"
                    >
                        <?php echo e($parent->name); ?>

                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($parent->children->isNotEmpty()): ?>
                        <ul class="ml-3 mt-0.5 space-y-0.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $parent->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <button
                                        wire:click="selectCategory('<?php echo e($child->id); ?>')"
                                        class="w-full rounded px-2 py-1 text-left text-xs hover:bg-gray-100 <?php echo e($categoryId === (string)$child->id ? 'bg-blue-50 font-semibold text-blue-700' : 'text-gray-600'); ?>"
                                    >
                                        <?php echo e($child->name); ?>

                                    </button>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>
    </aside>

    
    <div class="flex-1 min-w-0">
        
        <div class="mb-4">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search articles..."
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('agent.knowledge.show', $article->slug)); ?>"
                   class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                    <h3 class="mb-1 font-semibold text-gray-900 line-clamp-2"><?php echo e($article->title); ?></h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($article->category): ?>
                        <span class="mb-2 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700">
                            <?php echo e($article->category->name); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="text-sm text-gray-500 line-clamp-3">
                        <?php echo e(Str::limit(strip_tags($article->body), 120)); ?>

                    </p>
                    <p class="mt-2 text-xs text-gray-400"><?php echo e($article->created_at->diffForHumans()); ?></p>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-3 py-12 text-center text-gray-400">No articles found.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="mt-4">
            <?php echo e($articles->links()); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/livewire/knowledge/article-list.blade.php ENDPATH**/ ?>