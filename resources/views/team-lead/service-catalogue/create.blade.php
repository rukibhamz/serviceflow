@extends('layouts.team-lead')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">New Service Catalogue Item</h1>
        <a href="{{ route('team-lead.service-catalogue.index') }}" class="btn-ds ghost">Back</a>
    </div>

    <form method="POST" action="{{ route('team-lead.service-catalogue.store') }}" class="space-y-4 rounded border border-slate-200 bg-white p-4">
        @csrf

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-input-ds w-full">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
            <textarea name="description" rows="3" class="form-input-ds w-full">{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Type</label>
                <select name="type" class="form-input-ds w-full">
                    <option value="service_request" @selected(old('type') === 'service_request')>Service Request</option>
                    <option value="incident" @selected(old('type') === 'incident')>Incident</option>
                    <option value="problem" @selected(old('type') === 'problem')>Problem</option>
                    <option value="change" @selected(old('type') === 'change')>Change</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Priority</label>
                <select name="priority" class="form-input-ds w-full">
                    <option value="low" @selected(old('priority') === 'low')>Low</option>
                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                    <option value="high" @selected(old('priority') === 'high')>High</option>
                    <option value="urgent" @selected(old('priority') === 'urgent')>Urgent</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Team</label>
                <select name="team_id" class="form-input-ds w-full">
                    <option value="">All teams</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" @selected((string) old('team_id') === (string) $team->id)>{{ $team->name }}</option>
                    @endforeach
                </select>
                @error('team_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Custom Fields (JSON array, optional)</label>
            <textarea name="fields_json" rows="6" class="form-input-ds w-full" placeholder='[{"name":"justification","label":"Business Justification","type":"textarea","required":true}]'>{{ old('fields_json') }}</textarea>
            @error('fields_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1'))>
            Active
        </label>

        <div>
            <button type="submit" class="btn-ds primary">Create Item</button>
        </div>
    </form>
@endsection

