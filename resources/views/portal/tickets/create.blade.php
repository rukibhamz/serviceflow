@extends('portal.layout')

@section('title', 'Submit a Ticket')

@section('content')
    <a href="{{ route('portal.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Dashboard
    </a>

    <h1 class="mb-6 text-xl font-bold">Submit a Support Ticket</h1>

    <form method="POST" action="{{ route('portal.tickets.store') }}" enctype="multipart/form-data" class="space-y-4 rounded border border-gray-200 bg-white p-6">
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

        {{-- Attachments --}}
        <div
            x-data="{
                files: [],
                addFiles(e) {
                    const newFiles = Array.from(e.target.files);
                    this.files = [...this.files, ...newFiles].slice(0, 5);
                },
                remove(idx) {
                    this.files.splice(idx, 1);
                },
                formatSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
                    return (bytes/1048576).toFixed(1) + ' MB';
                },
                isImage(file) {
                    return file.type.startsWith('image/');
                }
            }"
        >
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Attachments
                <span class="ml-1 text-xs font-normal text-gray-400">(optional — up to 5 files, max 10 MB each)</span>
            </label>

            {{-- Drop zone --}}
            <label
                class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition"
                @dragover.prevent
                @drop.prevent="addFiles($event.dataTransfer)"
            >
                <div class="flex flex-col items-center gap-1 text-gray-400 pointer-events-none">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-sm">Drag & drop files here, or <span class="text-blue-600 underline">browse</span></span>
                    <span class="text-xs">PNG, JPG, GIF, PDF, DOC, XLS, ZIP</span>
                </div>
                <input
                    type="file"
                    name="attachments[]"
                    multiple
                    accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.zip"
                    class="hidden"
                    @change="addFiles($event)"
                />
            </label>

            {{-- File preview list --}}
            <div class="mt-2 space-y-2" x-show="files.length > 0">
                <template x-for="(file, idx) in files" :key="idx">
                    <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2">
                        {{-- Thumbnail or icon --}}
                        <template x-if="isImage(file)">
                            <img :src="URL.createObjectURL(file)" class="w-10 h-10 rounded object-cover flex-shrink-0">
                        </template>
                        <template x-if="!isImage(file)">
                            <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </template>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="file.name"></p>
                            <p class="text-xs text-gray-400" x-text="formatSize(file.size)"></p>
                        </div>
                        <button type="button" @click="remove(idx)"
                                class="text-gray-400 hover:text-red-500 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            @error('attachments.*')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Submit Ticket
        </button>
    </form>
@endsection
