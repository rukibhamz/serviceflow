<div class="space-y-6">

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Automation Rules</h2>
        <a href="{{ route((request()->routeIs('admin.*') ? 'admin' : 'agent') . '.automation.index', ['new' => 1]) }}" class="btn-ds primary">+ New Automation</a>
    </div>

    {{-- Form --}}
    @if ($showForm)
    <form method="POST" action="{{ route((request()->routeIs('admin.*') ? 'admin' : 'agent') . '.automation.save') }}" wire:submit.prevent="save" class="bg-white border border-gray-200 rounded-xl p-6 space-y-5 shadow-sm">
        @csrf
        <input type="hidden" name="editing_id" value="{{ $editingId }}">
        <h3 class="font-medium text-gray-700">{{ $editingId ? 'Edit Automation' : 'New Automation' }}</h3>

        {{-- Name + Trigger --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                <input wire:model="name" name="name" value="{{ $name }}" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Auto-assign critical tickets">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Trigger Event</label>
                <select wire:model="triggerEvent" name="trigger_event" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">— select trigger —</option>
                    @foreach ($triggers as $trigger)
                        <option value="{{ $trigger }}">{{ $trigger }}</option>
                    @endforeach
                </select>
                @error('triggerEvent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Conditions --}}
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-xs font-medium text-gray-600">Conditions</span>
                <select wire:model="condOperator" name="cond_operator" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="AND">ALL (AND)</option>
                    <option value="OR">ANY (OR)</option>
                </select>
                <button type="button" wire:click="addCondition" class="text-xs text-indigo-600 hover:underline">+ Add condition</button>
            </div>

            @foreach ($conditions as $i => $cond)
            <div class="flex items-center gap-2 mb-2">
                <select wire:model="conditions.{{ $i }}.field" name="conditions[{{ $i }}][field]" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="priority">priority</option>
                    <option value="status">status</option>
                    <option value="type">type</option>
                    <option value="subject">subject</option>
                    <option value="assignee_id">assignee_id</option>
                </select>
                <select wire:model="conditions.{{ $i }}.op" name="conditions[{{ $i }}][op]" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="equals">equals</option>
                    <option value="not_equals">not equals</option>
                    <option value="contains">contains</option>
                    <option value="not_contains">not contains</option>
                    <option value="is_null">is null</option>
                    <option value="is_not_null">is not null</option>
                </select>
                <input wire:model="conditions.{{ $i }}.value" name="conditions[{{ $i }}][value]" type="text"
                       class="border border-gray-300 rounded px-2 py-1 text-xs w-32" placeholder="value">
                <button type="button" wire:click="removeCondition({{ $i }})" class="text-red-400 hover:text-red-600 text-xs">✕</button>
            </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div>
            <div class="flex items-center gap-3 mb-2">
                <span class="text-xs font-medium text-gray-600">Actions</span>
                <button type="button" wire:click="addAction" class="text-xs text-indigo-600 hover:underline">+ Add action</button>
            </div>

            @foreach ($actions as $i => $action)
            <div class="flex items-start gap-2 mb-2">
                <select wire:model="actions.{{ $i }}.type" name="actions[{{ $i }}][type]" class="border border-gray-300 rounded px-2 py-1 text-xs">
                    <option value="assign_ticket">Assign ticket</option>
                    <option value="change_status">Change status</option>
                    <option value="add_comment">Add comment</option>
                    <option value="send_notification">Send notification</option>
                    <option value="trigger_webhook">Trigger webhook</option>
                </select>

                @if ($action['type'] === 'add_comment')
                    <input wire:model="actions.{{ $i }}.body" name="actions[{{ $i }}][body]" type="text"
                           class="border border-gray-300 rounded px-2 py-1 text-xs flex-1" placeholder="Comment body">
                @elseif ($action['type'] === 'change_status')
                    <input wire:model="actions.{{ $i }}.status" name="actions[{{ $i }}][status]" type="text"
                           class="border border-gray-300 rounded px-2 py-1 text-xs w-32" placeholder="new status">
                @elseif ($action['type'] === 'assign_ticket')
                    <input wire:model="actions.{{ $i }}.assignee_id" name="actions[{{ $i }}][assignee_id]" type="number"
                           class="border border-gray-300 rounded px-2 py-1 text-xs w-24" placeholder="user ID">
                @elseif ($action['type'] === 'trigger_webhook')
                    <input wire:model="actions.{{ $i }}.url" name="actions[{{ $i }}][url]" type="url"
                           class="border border-gray-300 rounded px-2 py-1 text-xs flex-1" placeholder="https://...">
                @elseif ($action['type'] === 'send_notification')
                    <input wire:model="actions.{{ $i }}.user_id" name="actions[{{ $i }}][user_id]" type="number"
                           class="border border-gray-300 rounded px-2 py-1 text-xs w-24" placeholder="user ID">
                    <input wire:model="actions.{{ $i }}.message" name="actions[{{ $i }}][message]" type="text"
                           class="border border-gray-300 rounded px-2 py-1 text-xs flex-1" placeholder="message">
                @endif

                <button type="button" wire:click="removeAction({{ $i }})" class="text-red-400 hover:text-red-600 text-xs mt-1">✕</button>
            </div>
            @endforeach
        </div>

        {{-- Active toggle + buttons --}}
        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input wire:model="isActive" name="is_active" type="checkbox" value="1" @checked($isActive) class="rounded border-gray-300">
                Active
            </label>
            <div class="flex gap-2">
                <a href="{{ route((request()->routeIs('admin.*') ? 'admin' : 'agent') . '.automation.index') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                <button type="submit" wire:loading.attr="disabled" class="btn-ds primary">Save</button>
            </div>
        </div>
    </form>
    @endif

    {{-- Automation list --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Trigger</th>
                    <th class="px-4 py-3 text-center">Runs</th>
                    <th class="px-4 py-3 text-center">Active</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($automations as $automation)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $automation->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $automation->trigger_event }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $automation->run_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route((request()->routeIs('admin.*') ? 'admin' : 'agent') . '.automation.toggle', $automation) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs px-2 py-0.5 rounded-full {{ $automation->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $automation->is_active ? 'On' : 'Off' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route((request()->routeIs('admin.*') ? 'admin' : 'agent') . '.automation.index', ['edit' => $automation->id]) }}" class="text-indigo-600 hover:underline text-xs">Edit</a>
                        <form method="POST" action="{{ route((request()->routeIs('admin.*') ? 'admin' : 'agent') . '.automation.delete', $automation) }}" class="inline" onsubmit="return confirm('Delete this automation?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No automations yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $automations->links() }}
        </div>
    </div>
</div>
