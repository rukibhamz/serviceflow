@extends('installer.layout')
@php $currentStep = 3; @endphp

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-4">Create Admin Account</h2>
<p class="text-gray-500 mb-6 text-sm">This account will have full administrator access to ServiceFlow.</p>

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('installer.account.store') }}" class="space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input type="password" name="password_confirmation"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="flex justify-between pt-2">
        <a href="{{ route('installer.database') }}" class="text-sm text-gray-500 hover:underline self-center">← Back</a>
        <button type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
            Create Account →
        </button>
    </div>
</form>
@endsection
