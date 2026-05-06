<div class="max-w-3xl mx-auto card-ds">
    <div class="card-body p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $type === 'change' ? 'Create Change Request' : ($type === 'problem' ? 'Add New Problem' : 'Create New Ticket') }}</h1>

    @php $routePrefix = request()->is('admin/*') ? 'admin' : 'agent'; @endphp
    <form method="POST" action="{{ route($routePrefix . '.tickets.store') }}" enctype="multipart/form-data" wire:submit.prevent="save" class="space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Requester</label>
            <select wire:model="requester_id" name="requester_id" class="form-input-ds mt-1">
                <option value="">Select Requester</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @error('requester_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select wire:model="type" name="type" class="form-input-ds mt-1">
                    <option value="incident" @selected($type === 'incident')>Incident</option>
                    <option value="service_request" @selected($type === 'service_request')>Service Request</option>
                    <option value="problem" @selected($type === 'problem')>Problem</option>
                    <option value="change" @selected($type === 'change')>Change</option>
                </select>
                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Priority</label>
                <select wire:model="priority" name="priority" class="form-input-ds mt-1">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                @error('priority') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">
                Team
                @if(in_array($type, ['problem', 'change'], true))
                    <span class="text-red-500">*</span>
                @endif
            </label>
            <select wire:model="team_id" name="team_id" class="form-input-ds mt-1">
                <option value="">Select Team</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('team_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input type="text" wire:model="subject" name="subject" class="form-input-ds mt-1" placeholder="Brief summary of the issue">
            @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea wire:model="description" name="description" rows="5" class="form-input-ds mt-1" placeholder="Detailed description of the request or incident"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Attachments</label>
            <input type="file" wire:model="attachments" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="form-input-ds mt-1" />
            <p class="mt-1 text-xs text-gray-500">Upload screenshots/images or documents (.pdf, .doc, .docx), max 10MB each.</p>
            @error('attachments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

            @if(!empty($attachments))
                <ul class="mt-2 space-y-1 text-xs text-gray-600">
                    @foreach($attachments as $file)
                        <li>{{ $file->getClientOriginalName() }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="btn-ds primary">
                {{ $type === 'change' ? 'Create Change Request' : ($type === 'problem' ? 'Add New Problem' : 'Create Ticket') }}
            </button>
        </div>
    </form>
</div>
</div>
