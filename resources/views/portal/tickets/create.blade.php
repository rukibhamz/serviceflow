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

        {{-- Tag colleagues / supervisors --}}
        <div
            x-data="{
                open: false,
                search: '',
                selected: [],
                people: {{ Js::from($colleagues->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])) }},
                get filtered() {
                    if (!this.search) return this.people;
                    const q = this.search.toLowerCase();
                    return this.people.filter(p => p.name.toLowerCase().includes(q) || p.email.toLowerCase().includes(q));
                },
                toggle(person) {
                    const idx = this.selected.findIndex(s => s.id === person.id);
                    if (idx >= 0) this.selected.splice(idx, 1);
                    else this.selected.push(person);
                    this.search = '';
                },
                isSelected(id) { return this.selected.some(s => s.id === id); },
                remove(id) { this.selected = this.selected.filter(s => s.id !== id); }
            }"
            @click.outside="open = false"
            class="relative"
        >
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Tag Colleagues / Supervisors
                <span class="ml-1 text-xs font-normal text-gray-400">(optional — they'll be kept in the loop)</span>
            </label>

            {{-- Tag chips + input --}}
            <div
                @click="open = true; $nextTick(() => $refs.search.focus())"
                class="min-h-[38px] w-full cursor-text rounded border border-gray-300 bg-white px-2 py-1.5 flex flex-wrap gap-1.5 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500"
            >
                <template x-for="person in selected" :key="person.id">
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">
                        <span x-text="person.name"></span>
                        <button type="button" @click.stop="remove(person.id)" class="hover:text-blue-600 leading-none">&times;</button>
                        <input type="hidden" :name="'tagged_users[]'" :value="person.id" />
                    </span>
                </template>
                <input
                    x-ref="search"
                    x-model="search"
                    @focus="open = true"
                    @keydown.escape="open = false"
                    type="text"
                    placeholder="Search by name or email..."
                    class="flex-1 min-w-[140px] border-none outline-none text-sm bg-transparent py-0.5"
                />
            </div>

            {{-- Dropdown --}}
            <div
                x-show="open && filtered.length > 0"
                x-transition
                class="absolute z-20 mt-1 w-full rounded border border-gray-200 bg-white shadow-lg max-h-52 overflow-y-auto"
            >
                <template x-for="person in filtered" :key="person.id">
                    <button
                        type="button"
                        @click="toggle(person); open = false"
                        class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm hover:bg-gray-50"
                        :class="isSelected(person.id) ? 'bg-blue-50' : ''"
                    >
                        <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white"
                              x-text="person.name.substring(0,2).toUpperCase()"></span>
                        <span class="flex-1 min-w-0">
                            <span class="block font-medium text-gray-900" x-text="person.name"></span>
                            <span class="block text-xs text-gray-400 truncate" x-text="person.email"></span>
                        </span>
                        <svg x-show="isSelected(person.id)" class="h-4 w-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </template>
            </div>

            <p x-show="open && search && filtered.length === 0" class="mt-1 text-xs text-gray-400">No users found.</p>
        </div>

        <button type="submit"
                class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Submit Ticket
        </button>
    </form>
@endsection
