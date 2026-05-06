<div class="space-y-4">

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm">{{ session('success') }}</div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="search" type="search"
               placeholder="Search users…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64">
        <div class="ml-auto">
            <button type="button" wire:click="startInvite" class="btn-ds primary inline-flex items-center">
                + Invite User
            </button>
        </div>
    </div>

    {{-- Invite form --}}
    @if ($showInviteForm)
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Invite New User</div></div>
        <div class="card-body">
            <form wire:submit.prevent="sendInvite" class="space-y-4">
                @error('inviteGeneral') <p class="rounded bg-red-100 px-3 py-2 text-sm text-red-700">{{ $message }}</p> @enderror
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 form-group">
                        <label class="form-label">Email Address *</label>
                        <input wire:model.defer="inviteEmail" type="email" class="form-input-ds" placeholder="user@example.com">
                        @error('inviteEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select wire:model.defer="inviteRole" class="form-input-ds">
                            <option value="user">User (Portal)</option>
                            <option value="agent">Agent</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
                    <button type="button" wire:click="$set('showInviteForm', false)" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-ds primary">Send Invitation</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit user panel --}}
    @if ($editingUserId)
    <div class="card-ds border-blue-200">
        <div class="card-hdr"><div class="card-title">Edit User</div></div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input wire:model="editName" type="text" class="form-input-ds">
                    @error('editName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input wire:model="editEmail" type="email" class="form-input-ds">
                    @error('editEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select wire:model="editRole" class="form-input-ds">
                        <option value="user">User (Portal)</option>
                        <option value="agent">Agent</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Teams</label>
                    <div class="space-y-1 max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-2">
                        @foreach($teams as $team)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                            <input type="checkbox" wire:model="editTeams" value="{{ $team->id }}" class="rounded border-gray-300">
                            {{ $team->name }}
                        </label>
                        @endforeach
                        @if($teams->isEmpty())
                            <p class="text-xs text-gray-400 p-1">No teams yet.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input wire:model="editIsActive" type="checkbox" class="rounded border-gray-300">
                    Active account
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" wire:click="$set('editingUserId', null)" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" wire:click="saveUser" class="btn-ds primary">Save Changes</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Pending invitations --}}
    @if($pendingInvitations->isNotEmpty())
    <div class="card-ds">
        <div class="card-hdr">
            <div class="card-title">Pending Invitations</div>
            <span class="text-xs text-gray-400">{{ $pendingInvitations->count() }} pending</span>
        </div>
        <div class="card-body p-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Role</th>
                        <th class="px-4 py-2 text-left">Invited By</th>
                        <th class="px-4 py-2 text-left">Expires</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($pendingInvitations as $inv)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-700">{{ $inv->email }}</td>
                        <td class="px-4 py-2"><span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($inv->role) }}</span></td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $inv->inviter?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-400 text-xs">{{ $inv->expires_at->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-right">
                            <button wire:click="cancelInvitation({{ $inv->id }})" wire:confirm="Cancel this invitation?" class="text-xs text-red-500 hover:underline">Cancel</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Users table --}}
    <div class="card-ds">
        <div class="card-body p-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Teams</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Joined</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 {{ !$user->is_active ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-brand flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ match($user->role) {
                                'admin' => 'bg-purple-100 text-purple-700',
                                'agent' => 'bg-blue-100 text-blue-700',
                                default => 'bg-gray-100 text-gray-600',
                            } }}">{{ ucfirst($user->role ?? 'user') }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $user->teams->pluck('name')->join(', ') ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="editUser({{ $user->id }})" class="text-xs text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="toggleActive({{ $user->id }})"
                                    class="text-xs {{ $user->is_active ? 'text-orange-500' : 'text-green-600' }} hover:underline">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">{{ $users->links() }}</div>
        </div>
    </div>
</div>
