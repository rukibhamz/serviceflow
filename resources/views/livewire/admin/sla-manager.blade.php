<div class="space-y-4">

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Define response and resolution time targets per priority and ticket type.</p>
        </div>
        <a href="{{ route('admin.sla', ['new' => 1]) }}"
                class="btn-ds primary inline-flex items-center">
            + New SLA Policy
        </a>
    </div>

    {{-- Form --}}
    @if ($showForm)
    <div class="card-ds">
        <div class="card-hdr">
            <div class="card-title">{{ $editingId ? 'Edit SLA Policy' : 'New SLA Policy' }}</div>
        </div>
        <div class="card-body space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Policy Name *</label>
                    <input wire:model="name" type="text" class="form-input-ds" placeholder="e.g. High Priority SLA">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Priority *</label>
                    <select wire:model="priority" class="form-input-ds">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ticket Type <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select wire:model="ticketType" class="form-input-ds">
                        <option value="">All types</option>
                        <option value="incident">Incident</option>
                        <option value="service_request">Service Request</option>
                        <option value="problem">Problem</option>
                        <option value="change">Change</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">First Response Target *</label>
                    <div class="flex items-center gap-2">
                        <input wire:model="responseMinutes" type="number" min="1" class="form-input-ds w-28">
                        <span class="text-xs text-gray-400">minutes</span>
                        @if($responseMinutes >= 60)
                            <span class="text-xs text-blue-500">({{ intdiv($responseMinutes,60) }}h {{ $responseMinutes%60 > 0 ? ($responseMinutes%60).'m' : '' }})</span>
                        @endif
                    </div>
                    @error('responseMinutes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Resolution Target *</label>
                    <div class="flex items-center gap-2">
                        <input wire:model="resolutionMinutes" type="number" min="1" class="form-input-ds w-28">
                        <span class="text-xs text-gray-400">minutes</span>
                        @if($resolutionMinutes >= 60)
                            <span class="text-xs text-blue-500">({{ intdiv($resolutionMinutes,60) }}h {{ $resolutionMinutes%60 > 0 ? ($resolutionMinutes%60).'m' : '' }})</span>
                        @endif
                    </div>
                    @error('resolutionMinutes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-6 pt-2">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input wire:model="businessHoursOnly" type="checkbox" class="rounded border-gray-300">
                    Business hours only (Mon–Fri 9am–5pm)
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input wire:model="isDefault" type="checkbox" class="rounded border-gray-300">
                    Set as default for this priority/type
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input wire:model="isActive" type="checkbox" class="rounded border-gray-300">
                    Active
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="button" wire:click="save" class="btn-ds primary">Save Policy</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Policies table --}}
    <div class="card-ds">
        <div class="card-body p-0">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Priority</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-center">Response</th>
                        <th class="px-4 py-3 text-center">Resolution</th>
                        <th class="px-4 py-3 text-center">Biz Hours</th>
                        <th class="px-4 py-3 text-center">Default</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($policies as $policy)
                    @php
                        $rh = intdiv($policy->response_minutes, 60);
                        $rm = $policy->response_minutes % 60;
                        $xh = intdiv($policy->resolution_minutes, 60);
                        $xm = $policy->resolution_minutes % 60;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $policy->name }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ match($policy->priority) {
                                'critical','urgent' => 'bg-red-100 text-red-700',
                                'high'              => 'bg-orange-100 text-orange-700',
                                'medium'            => 'bg-yellow-100 text-yellow-700',
                                default             => 'bg-gray-100 text-gray-600',
                            } }}">{{ ucfirst($policy->priority) }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $policy->ticket_type ? ucfirst(str_replace('_',' ',$policy->ticket_type)) : 'All' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $rh > 0 ? $rh.'h' : '' }}{{ $rm > 0 ? ' '.$rm.'m' : '' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $xh > 0 ? $xh.'h' : '' }}{{ $xm > 0 ? ' '.$xm.'m' : '' }}</td>
                        <td class="px-4 py-3 text-center">{{ $policy->business_hours ? '✓' : '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($policy->is_default)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Default</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $policy->id }})"
                                    class="text-xs px-2 py-0.5 rounded-full {{ $policy->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="edit({{ $policy->id }})" class="text-xs text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $policy->id }})" wire:confirm="Delete this SLA policy?" class="text-xs text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">No SLA policies yet. Create one to start tracking response times.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">{{ $policies->links() }}</div>
        </div>
    </div>
</div>
