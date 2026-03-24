@extends('layouts.agent')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">My Profile</h2>
        <p class="text-sm text-gray-500 mt-1">Manage your account details</p>
    </div>

    <div class="max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-4 mb-6">
            <div class="h-14 w-14 rounded-full bg-blue-600 flex items-center justify-center text-white text-xl font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                <span class="inline-block mt-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 capitalize">
                    {{ auth()->user()->getRoleNames()->first() ?? 'agent' }}
                </span>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" value="{{ auth()->user()->name }}" disabled
                       class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" value="{{ auth()->user()->email }}" disabled
                       class="w-full rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
            </div>
        </div>

        <p class="mt-4 text-xs text-gray-400">Profile editing coming soon.</p>
    </div>
@endsection
