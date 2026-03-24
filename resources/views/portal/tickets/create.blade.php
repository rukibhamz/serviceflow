@extends('portal.layout')

@section('title', 'Submit a Ticket')

@section('content')
    <a href="{{ route('portal.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Dashboard
    </a>

    <h1 class="mb-6 text-xl font-bold">Submit a Support Ticket</h1>

    <form method="POST" action="{{ route('portal.tickets.store') }}" class="space-y-4 rounded border border-gray-200 bg-white p-6">
        @csrf

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
            <input name="subject" type="text" value="{{ old('subject') }}" required
                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="5"
                      class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
        </div>

        <div class="flex gap-4">
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-gray-700">Priority</label>
                <select name="priority" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="low" @selected(old('priority') === 'low')>Low</option>
                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                    <option value="high" @selected(old('priority') === 'high')>High</option>
                    <option value="critical" @selected(old('priority') === 'critical')>Critical</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    <option value="incident" @selected(old('type', 'incident') === 'incident')>Incident</option>
                    <option value="service_request" @selected(old('type') === 'service_request')>Service Request</option>
                    <option value="problem" @selected(old('type') === 'problem')>Problem</option>
                    <option value="change" @selected(old('type') === 'change')>Change</option>
                </select>
            </div>
        </div>

        <button type="submit"
                class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Submit Ticket
        </button>
    </form>
@endsection
