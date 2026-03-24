@extends('layouts.agent')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Asset Management</h2>
            <p class="text-sm text-gray-500 mt-1">Track hardware, software and configuration items</p>
        </div>
        <button class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ New Asset</button>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
        <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
        </svg>
        <p class="text-gray-500 font-medium">No assets registered</p>
        <p class="text-sm text-gray-400 mt-1">Assets will appear here once added to the inventory.</p>
    </div>
@endsection
