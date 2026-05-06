<div class="max-w-3xl mx-auto card-ds">
    <div class="card-body p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Ticket</h1>

    <form wire:submit.prevent="save" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Requester</label>
            <select wire:model="requester_id" class="form-input-ds mt-1">
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
                <select wire:model="type" class="form-input-ds mt-1">
                    <option value="incident">Incident</option>
                    <option value="service_request">Service Request</option>
                    <option value="problem">Problem</option>
                    <option value="change">Change</option>
                </select>
                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Priority</label>
                <select wire:model="priority" class="form-input-ds mt-1">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                @error('priority') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Subject</label>
            <input type="text" wire:model="subject" class="form-input-ds mt-1" placeholder="Brief summary of the issue">
            @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea wire:model="description" rows="5" class="form-input-ds mt-1" placeholder="Detailed description of the request or incident"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="btn-ds primary">
                Create Ticket
            </button>
        </div>
    </form>
</div>
</div>
