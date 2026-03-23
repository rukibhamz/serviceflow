<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Team Management</h1>
        <button wire:click="$set('isCreating', true)" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Create New Team
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($isCreating)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">{{ $editingTeamId ? 'Edit Team' : 'Create Team' }}</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Team Name</label>
                        <input type="text" wire:model="name" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea wire:model="description" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('isCreating', false)" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</button>
                    <button wire:click="{{ $editingTeamId ? 'updateTeam' : 'createTeam' }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        {{ $editingTeamId ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Member Management Modal --}}
    @if($selectedTeamId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">Manage Team Members</h2>
                <div class="max-h-96 overflow-y-auto space-y-2 pr-2">
                    @foreach($allAgents as $agent)
                        <label class="flex items-center gap-3 rounded border border-gray-100 p-2 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" wire:model="selectedAgents" value="{{ $agent->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $agent->name }}</p>
                                <p class="text-xs text-gray-500">{{ $agent->email }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('selectedTeamId', 0)" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</button>
                    <button wire:click="saveMembers" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Save Members
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Team Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Members</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($teams as $team)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $team->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $team->description ?: '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $team->members_count }} agents</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                            <button wire:click="manageMembers({{ $team->id }})" class="text-blue-600 hover:text-blue-900">Members</button>
                            <button wire:click="editTeam({{ $team->id }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <button wire:click="deleteTeam({{ $team->id }})" class="text-red-600 hover:text-red-900" onclick="confirm('Delete this team?') || event.stopImmediatePropagation()">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $teams->links() }}
        </div>
    </div>
</div>
