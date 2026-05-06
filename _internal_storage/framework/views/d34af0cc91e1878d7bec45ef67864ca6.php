<?php $__env->startSection('title', 'Submit a Ticket'); ?>

<?php $__env->startSection('content'); ?>
    <a href="<?php echo e(route('portal.index')); ?>" class="mb-4 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Dashboard
    </a>

    <h1 class="mb-6 text-xl font-bold">Submit a Support Ticket</h1>

    <form method="POST" action="<?php echo e(route('portal.tickets.store')); ?>" class="space-y-4 rounded border border-gray-200 bg-white p-6">
        <?php echo csrf_field(); ?>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
            <input name="subject" type="text" value="<?php echo e(old('subject')); ?>" required
                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="5"
                      class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('description')); ?></textarea>
        </div>

        <div class="flex gap-4">
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-gray-700">Priority</label>
                <select name="priority" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="low" <?php if(old('priority') === 'low'): echo 'selected'; endif; ?>>Low</option>
                    <option value="medium" <?php if(old('priority', 'medium') === 'medium'): echo 'selected'; endif; ?>>Medium</option>
                    <option value="high" <?php if(old('priority') === 'high'): echo 'selected'; endif; ?>>High</option>
                    <option value="critical" <?php if(old('priority') === 'critical'): echo 'selected'; endif; ?>>Critical</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="incident" <?php if(old('type', 'incident') === 'incident'): echo 'selected'; endif; ?>>Incident</option>
                    <option value="service_request" <?php if(old('type') === 'service_request'): echo 'selected'; endif; ?>>Service Request</option>
                    <option value="problem" <?php if(old('type') === 'problem'): echo 'selected'; endif; ?>>Problem</option>
                    <option value="change" <?php if(old('type') === 'change'): echo 'selected'; endif; ?>>Change</option>
                </select>
            </div>
        </div>

        
        <div
            x-data="{
                open: false,
                search: '',
                selected: [],
                people: <?php echo e(Js::from($colleagues->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]))); ?>,
                get filtered() {
                    if (!this.search) return this.people;
                    const q = this.search.toLowerCase();
                    return this.people.filter(p => p.name.toLowerCase().includes(q) || p.email.toLowerCase().includes(q));
                },
                toggle(person) {
                    const idx = this.selected.findIndex(s => s.id === person.id);
                    if (idx >= 0) this.selected.splice(idx, 1);
                    else this.selected.push(person);
                    this.search = '';
                },
                isSelected(id) { return this.selected.some(s => s.id === id); },
                remove(id) { this.selected = this.selected.filter(s => s.id !== id); }
            }"
            @click.outside="open = false"
            class="relative"
        >
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Tag Colleagues / Supervisors
                <span class="ml-1 text-xs font-normal text-gray-400">(optional — they'll be kept in the loop)</span>
            </label>

            
            <div
                @click="open = true; $nextTick(() => $refs.search.focus())"
                class="min-h-[38px] w-full cursor-text rounded border border-gray-300 bg-white px-2 py-1.5 flex flex-wrap gap-1.5 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500"
            >
                <template x-for="person in selected" :key="person.id">
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">
                        <span x-text="person.name"></span>
                        <button type="button" @click.stop="remove(person.id)" class="hover:text-blue-600 leading-none">&times;</button>
                        <input type="hidden" :name="'tagged_users[]'" :value="person.id" />
                    </span>
                </template>
                <input
                    x-ref="search"
                    x-model="search"
                    @focus="open = true"
                    @keydown.escape="open = false"
                    type="text"
                    placeholder="Search by name or email..."
                    class="flex-1 min-w-[140px] border-none outline-none text-sm bg-transparent py-0.5"
                />
            </div>

            
            <div
                x-show="open && filtered.length > 0"
                x-transition
                class="absolute z-20 mt-1 w-full rounded border border-gray-200 bg-white shadow-lg max-h-52 overflow-y-auto"
            >
                <template x-for="person in filtered" :key="person.id">
                    <button
                        type="button"
                        @click="toggle(person); open = false"
                        class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm hover:bg-gray-50"
                        :class="isSelected(person.id) ? 'bg-blue-50' : ''"
                    >
                        <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white"
                              x-text="person.name.substring(0,2).toUpperCase()"></span>
                        <span class="flex-1 min-w-0">
                            <span class="block font-medium text-gray-900" x-text="person.name"></span>
                            <span class="block text-xs text-gray-400 truncate" x-text="person.email"></span>
                        </span>
                        <svg x-show="isSelected(person.id)" class="h-4 w-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </template>
            </div>

            <p x-show="open && search && filtered.length === 0" class="mt-1 text-xs text-gray-400">No users found.</p>
        </div>

        <button type="submit"
                class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Submit Ticket
        </button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('portal.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/portal/tickets/create.blade.php ENDPATH**/ ?>