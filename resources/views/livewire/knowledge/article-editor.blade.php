<div>
    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        {{-- Title --}}
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
            <input
                wire:model="title"
                type="text"
                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Article title"
            />
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Body (Markdown) --}}
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Body (Markdown)</label>
            <textarea
                wire:model="body"
                rows="12"
                class="w-full rounded border border-gray-300 px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Write your article in Markdown..."
            ></textarea>
            @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Category --}}
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
            <select wire:model="categoryId" class="rounded border border-gray-300 px-3 py-2 text-sm">
                <option value="">— No category —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}@if($category->team) — {{ $category->team->name }}@endif
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- New category --}}
        <div class="rounded border border-gray-200 bg-gray-50 p-3 space-y-3">
            <p class="text-sm font-medium text-gray-700">Create Category</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <input wire:model.defer="newCategoryName" type="text" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" placeholder="Category name" />
                    @error('newCategoryName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                @if(auth()->user()?->hasRole('admin') || auth()->user()?->role === 'admin')
                    <div>
                        <select wire:model.defer="newCategoryTeamId" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Select team</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        @error('newCategoryTeamId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
            <div>
                <button wire:click="createCategory" type="button" class="rounded border border-blue-300 bg-white px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50">
                    + Add Category
                </button>
            </div>
        </div>

        {{-- Status --}}
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select wire:model="status" class="rounded border border-gray-300 px-3 py-2 text-sm">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
            </select>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <button
                wire:click="save"
                class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Save
            </button>

            @if($article && $article->exists && $article->status !== 'published')
                <button
                    wire:click="publish"
                    class="rounded bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                >
                    Publish
                </button>
            @endif
        </div>
    </div>
</div>
