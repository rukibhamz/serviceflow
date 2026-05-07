<div class="space-y-4">
    @php $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'agent'; @endphp

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm">{{ session('success') }}</div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="search" type="search"
               placeholder="Search assets…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56">
        <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)
                <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>
        <div class="ml-auto flex gap-2">
            <button type="button" wire:click="$set('showImport', true)"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Import CSV</button>
            <a href="{{ route($routePrefix . '.assets.index', ['new' => 1]) }}"
                    class="btn-ds primary inline-flex items-center">+ New Asset</a>
        </div>
    </div>

    {{-- Create / Edit form --}}
    @if ($showForm)
    <form method="POST" action="{{ route($routePrefix . '.assets.save') }}" wire:submit.prevent="save" class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
        @csrf
        <input type="hidden" name="editing_id" value="{{ $editingId }}">
        <h3 class="font-medium text-gray-700">{{ $editingId ? 'Edit Asset' : 'New Asset' }}</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                <input wire:model="name" name="name" value="{{ $name }}" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type *</label>
                <input wire:model="type" name="type" value="{{ $type }}" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="laptop, monitor, phone…">
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Serial Number</label>
                <input wire:model="serialNumber" name="serial_number" value="{{ $serialNumber }}" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Asset Tag</label>
                <input wire:model="assetTag" name="asset_tag" value="{{ $assetTag }}" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select wire:model="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Purchased At</label>
                <input wire:model="purchasedAt" name="purchased_at" value="{{ $purchasedAt }}" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route($routePrefix . '.assets.index') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-ds primary">Save</button>
        </div>
    </form>
    @endif

    {{-- Import wizard --}}
    @if ($showImport)
    <form method="POST" action="{{ route($routePrefix . '.assets.import') }}" enctype="multipart/form-data" wire:submit.prevent="runImport" class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
        @csrf
        <h3 class="font-medium text-gray-700">Import Assets from CSV</h3>
        <p class="text-xs text-gray-500">Required columns: <code>name, type</code>. Optional: <code>serial_number, asset_tag, status, purchased_at</code></p>
        <input wire:model="importFile" name="import_file" type="file" accept=".csv,.xlsx,.xls" class="text-sm">
        @error('importFile') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

        @if ($importCreated !== null)
            <p class="text-green-600 text-sm">✓ {{ $importCreated }} asset(s) imported.</p>
        @endif
        @if ($importErrors)
            <div class="text-red-600 text-xs space-y-1">
                @foreach ($importErrors as $row => $errs)
                    <p>Row {{ $row }}: {{ implode(', ', $errs) }}</p>
                @endforeach
            </div>
        @endif

        <div class="flex gap-2">
            <button type="submit" wire:loading.attr="disabled" class="btn-ds primary">Import</button>
            <a href="{{ route($routePrefix . '.assets.index') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Close</a>
        </div>
    </form>
    @endif

    {{-- Assignment panel --}}
    @if ($assigningId)
    <div class="bg-white border border-indigo-200 rounded-xl p-5 shadow-sm space-y-3">
        <h3 class="font-medium text-gray-700">Assign Asset</h3>
        <input wire:model.live.debounce.300ms="assigneeSearch" type="search"
               placeholder="Search users…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64">
        @foreach ($assigneeResults as $user)
            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                <span class="text-sm text-gray-700">{{ $user->name }} <span class="text-gray-400 text-xs">{{ $user->email }}</span></span>
                <button type="button" wire:click="assignTo({{ $user->id }})" class="text-xs text-indigo-600 hover:underline">Assign</button>
            </div>
        @endforeach
        <button type="button" wire:click="$set('assigningId', null)" class="text-xs text-gray-400 hover:underline">Cancel</button>
    </div>
    @endif

    {{-- Asset table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Serial</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Assigned To</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($assets as $asset)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $asset->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $asset->type }}</td>
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $asset->serial_number ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $asset->status === 'in_use' ? 'bg-blue-100 text-blue-700' :
                               ($asset->status === 'available' ? 'bg-green-100 text-green-700' :
                               ($asset->status === 'retired' ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-700')) }}">
                            {{ $asset->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $asset->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button type="button" wire:click="openAssign({{ $asset->id }})" class="text-xs text-indigo-600 hover:underline">Assign</button>
                        @if ($asset->assigned_to)
                            <form method="POST" action="{{ route($routePrefix . '.assets.unassign', $asset) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-orange-500 hover:underline">Unassign</button>
                            </form>
                        @endif
                        <a href="{{ route($routePrefix . '.assets.index', ['edit' => $asset->id]) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                        <form method="POST" action="{{ route($routePrefix . '.assets.delete', $asset) }}" class="inline" onsubmit="return confirm('Delete this asset?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No assets found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $assets->links() }}</div>
    </div>
</div>
