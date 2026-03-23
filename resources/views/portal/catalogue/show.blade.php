@extends('portal.layout')

@section('title', $item['name'])

@section('content')
    <a href="{{ route('portal.catalogue.index') }}" class="mb-4 inline-block text-sm text-blue-600 hover:underline">← Service Catalogue</a>

    <h1 class="mb-1 text-xl font-bold">{{ $item['name'] }}</h1>
    <p class="mb-6 text-sm text-gray-500">{{ $item['description'] }}</p>

    <form method="POST" action="{{ route('portal.catalogue.submit', $item['id']) }}"
          class="space-y-4 rounded border border-gray-200 bg-white p-6">
        @csrf

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
            <input name="subject" type="text" value="{{ old('subject', $item['name']) }}" required
                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3"
                      class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
        </div>

        @foreach($item['fields'] as $field)
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    {{ $field['label'] }}
                    @if($field['required']) <span class="text-red-500">*</span> @endif
                </label>

                @if($field['type'] === 'textarea')
                    <textarea name="{{ $field['name'] }}" rows="3" @if($field['required']) required @endif
                              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old($field['name']) }}</textarea>
                @elseif($field['type'] === 'select')
                    <select name="{{ $field['name'] }}" @if($field['required']) required @endif
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        <option value="">— Select —</option>
                        @foreach($field['options'] as $option)
                            <option value="{{ $option }}" @selected(old($field['name']) === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif($field['type'] === 'checkbox')
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="{{ $field['name'] }}" value="1"
                               @checked(old($field['name'])) class="rounded border-gray-300" />
                        {{ $field['label'] }}
                    </label>
                @else
                    <input type="text" name="{{ $field['name'] }}" value="{{ old($field['name']) }}"
                           @if($field['required']) required @endif
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                @endif
            </div>
        @endforeach

        <button type="submit"
                class="rounded bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Submit Request
        </button>
    </form>
@endsection
