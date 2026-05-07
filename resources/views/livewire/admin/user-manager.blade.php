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
            <a href="{{ route('admin.users', ['add' => 1]) }}" class="btn-ds primary inline-flex items-center mr-2">
                + Add User
            </a>
            <a href="{{ route('admin.users', ['new' => 1]) }}" class="btn-ds primary inline-flex items-center">
                + Invite User
            </a>
        </div>
    </div>

    {{-- Direct add user form --}}
    @if(request()->boolean('add') || (old('_form') === 'add_user' && $errors->any()))
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Add User</div></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_form" value="add_user">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input name="name" type="text" value="{{ old('name') }}" class="form-input-ds" required>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input name="email" type="email" value="{{ old('email') }}" class="form-input-ds" required>
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input name="password" type="password" class="form-input-ds" required>
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input name="password_confirmation" type="password" class="form-input-ds" required>
                    </div>
                    <div class="form-group sm:col-span-2">
                        <label class="form-label">Roles *</label>
                        <div class="space-y-1 max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-2">
                            @foreach(['end_user' => 'User (Portal)', 'agent' => 'Agent', 'team_lead' => 'Team Lead', 'manager' => 'Manager', 'admin' => 'Admin'] as $roleValue => $roleLabel)
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                                    <input type="checkbox" name="roles[]" value="{{ $roleValue }}" @checked(in_array($roleValue, old('roles', ['end_user']), true)) class="rounded border-gray-300">
                                    {{ $roleLabel }}
                                </label>
                            @endforeach
                        </div>
                        @error('roles') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('roles.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group flex items-end">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input name="is_active" type="checkbox" value="1" @checked(old('is_active', '1') === '1') class="rounded border-gray-300">
                            Active account
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.users') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="btn-ds primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Invite form --}}
    @if ($showInviteForm)
    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Invite New User</div></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.invitations.send') }}" wire:submit.prevent="sendInvite" class="space-y-4">
                @csrf
                @error('inviteGeneral') <p class="rounded bg-red-100 px-3 py-2 text-sm text-red-700">{{ $message }}</p> @enderror
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 form-group">
                        <label class="form-label">Email Address *</label>
                        <input wire:model.defer="inviteEmail" name="invite_email" value="{{ $inviteEmail }}" type="email" class="form-input-ds" placeholder="user@example.com">
                        @error('inviteEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('invite_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select wire:model.defer="inviteRole" name="invite_role" class="form-input-ds">
                            <option value="end_user" @selected($inviteRole === 'end_user')>User (Portal)</option>
                            <option value="agent" @selected($inviteRole === 'agent')>Agent</option>
                            <option value="team_lead" @selected($inviteRole === 'team_lead')>Team Lead</option>
                            <option value="manager" @selected($inviteRole === 'manager')>Manager</option>
                            <option value="admin" @selected($inviteRole === 'admin')>Admin</option>
                        </select>
                        @error('invite_role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.users') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
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
        <form method="POST" action="{{ route('admin.users.update', $editingUserId) }}" wire:submit.prevent="saveUser" class="card-body space-y-4">
            @csrf
            @method('PATCH')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input wire:model="editName" name="name" value="{{ $editName }}" type="text" class="form-input-ds">
                    @error('editName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input wire:model="editEmail" name="email" value="{{ $editEmail }}" type="email" class="form-input-ds">
                    @error('editEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Roles</label>
                    <div class="space-y-1 max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-2">
                        @foreach(['end_user' => 'User (Portal)', 'agent' => 'Agent', 'team_lead' => 'Team Lead', 'manager' => 'Manager', 'admin' => 'Admin'] as $roleValue => $roleLabel)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                            <input type="checkbox" wire:model="editRoles" name="roles[]" value="{{ $roleValue }}" @checked(in_array($roleValue, $editRoles, true)) class="rounded border-gray-300">
                            {{ $roleLabel }}
                        </label>
                        @endforeach
                    </div>
                    @error('editRoles') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('editRoles.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Teams</label>
                    <div class="space-y-1 max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-2">
                        @foreach($teams as $team)
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                            <input type="checkbox" wire:model="editTeams" name="teams[]" value="{{ $team->id }}" @checked(in_array((string) $team->id, $editTeams, true)) class="rounded border-gray-300">
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
                    <input wire:model="editIsActive" name="is_active" type="checkbox" value="1" @checked($editIsActive) class="rounded border-gray-300">
                    Active account
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('admin.users') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                <button type="submit" class="btn-ds primary">Save Changes</button>
            </div>
        </form>
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
                            <form method="POST" action="{{ route('admin.invitations.cancel', $inv) }}" class="inline" onsubmit="return confirm('Cancel this invitation?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Cancel</button>
                            </form>
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
                    @foreach($pendingInvitations as $inv)
                    <tr class="hover:bg-gray-50 bg-amber-50/40">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-amber-400 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                    IN
                                </div>
                                <span class="font-medium text-gray-800">Pending Invite</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $inv->email }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($inv->role) }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">—</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Invited</span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">Expires {{ $inv->expires_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <form method="POST" action="{{ route('admin.invitations.cancel', $inv) }}" class="inline" onsubmit="return confirm('Cancel this invitation?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
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
                            @php
                                $roleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [];
                                if (empty($roleNames) && !empty($user->role)) { $roleNames = [$user->role]; }
                            @endphp
                            <div class="flex flex-wrap gap-1">
                                @foreach($roleNames as $roleName)
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ match($roleName) {
                                        'admin' => 'bg-purple-100 text-purple-700',
                                        'agent' => 'bg-blue-100 text-blue-700',
                                        'team_lead' => 'bg-emerald-100 text-emerald-700',
                                        'manager' => 'bg-fuchsia-100 text-fuchsia-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    } }}">{{ ucfirst(str_replace('_', ' ', $roleName)) }}</span>
                                @endforeach
                            </div>
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
                            <a href="{{ route('admin.users', ['edit' => $user->id]) }}" class="text-xs text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.users.status.toggle', $user) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs {{ $user->is_active ? 'text-orange-500' : 'text-green-600' }} hover:underline">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
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
