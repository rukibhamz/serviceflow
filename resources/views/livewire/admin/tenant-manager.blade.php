<div class="space-y-6">

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Tenants</h2>
        <button wire:click="$set('showForm', true)"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            + Provision Tenant
        </button>
    </div>

    {{-- Provision form --}}
    @if ($showForm)
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 class="font-medium text-gray-700">New Tenant</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Organisation Name *</label>
                <input wire:model="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Subdomain *</label>
                <div class="flex items-center gap-1">
                    <input wire:model="subdomain" type="text" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="acme">
                    <span class="text-xs text-gray-400">.serviceflow.app</span>
                </div>
                @error('subdomain') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Admin Name *</label>
                <input wire:model="adminName" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('adminName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Admin Email *</label>
                <input wire:model="adminEmail" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('adminEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Admin Password *</label>
                <input wire:model="adminPassword" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('adminPassword') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button wire:click="$set('showForm', false)" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
            <button wire:click="provision" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Provision</button>
        </div>
    </div>
    @endif

    {{-- Tenant list --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Subdomain</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Created</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tenants as $tenant)
                <tr class="hover:bg-gray-50 {{ $tenant->trashed() ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $tenant->name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $tenant->subdomain }}.serviceflow.app</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $tenant->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $tenant->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        @if ($tenant->is_active)
                            <button wire:click="suspend({{ $tenant->id }})"
                                    wire:confirm="Suspend this tenant?"
                                    class="text-xs text-orange-500 hover:underline">Suspend</button>
                        @else
                            <button wire:click="activate({{ $tenant->id }})"
                                    class="text-xs text-green-600 hover:underline">Activate</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No tenants yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $tenants->links() }}</div>
    </div>
</div>
