@extends('portal.layout')

@section('title', 'Service Catalogue')

@section('content')
    <a href="{{ route('portal.index') }}" class="mb-4 inline-block text-sm text-blue-600 hover:underline">← Portal</a>

    <h1 class="mb-6 text-xl font-bold">Service Catalogue</h1>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach($items as $item)
            <a href="{{ route('portal.catalogue.show', $item['id']) }}"
               class="block rounded border border-gray-200 bg-white p-5 hover:shadow-md transition-shadow">
                <h2 class="mb-1 font-semibold text-gray-900">{{ $item['name'] }}</h2>
                <p class="mb-3 text-sm text-gray-500">{{ $item['description'] }}</p>
                <div class="flex gap-2 text-xs">
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">{{ $item['type'] }}</span>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-blue-700">{{ $item['priority'] }}</span>
                </div>
            </a>
        @endforeach
    </div>
@endsection
