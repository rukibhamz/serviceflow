<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Team Management</h1>
        <a href="{{ route('admin.teams', ['new' => 1]) }}" class="btn-ds primary inline-flex items-center">
            Create New Team
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded bg-red-100 px-4 py-2 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($isCreating)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">{{ $editingTeamId ? 'Edit Team' : 'Create Team' }}</h2>
                <form method="POST" action="{{ $editingTeamId ? route('admin.teams.update', $editingTeamId) : route('admin.teams.store') }}" wire:submit.prevent="saveTeam" class="space-y-4">
                    @csrf
                    @if($editingTeamId)
                        @method('PATCH')
                    @endif
                    @error('general') <p class="rounded bg-red-100 px-3 py-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Team Name</label>
                        <input type="text" wire:model.defer="name" name="name" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea wire:model.defer="description" name="description" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('admin.teams') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
                        <button type="submit" class="btn-ds primary">
                            {{ $editingTeamId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Member Management Modal --}}
    @if($selectedTeamId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">Manage Team Members</h2>
                <form method="POST" action="{{ route('admin.teams.members.update', $selectedTeamId) }}">
                    @csrf
                    <div class="max-h-96 overflow-y-auto space-y-2 pr-2">
                        @foreach($allAgents as $agent)
                            <label class="flex items-center gap-3 rounded border border-gray-100 p-2 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="selectedAgents[]" value="{{ $agent->id }}" @checked(in_array($agent->id, $selectedAgents, true)) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $agent->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $agent->email }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('admin.teams') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
                        <button type="submit" class="btn-ds primary">
                            Save Members
                        </button>
                    </div>
                </form>
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
                            <a href="{{ route('admin.teams', ['members' => $team->id]) }}" class="text-blue-600 hover:text-blue-900">Members</a>
                            <a href="{{ route('admin.teams', ['edit' => $team->id]) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" class="inline" onsubmit="return confirm('Delete this team?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
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
