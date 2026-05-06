@extends('layouts.agent')

@section('page-header')
    <div class="page-title">My Profile</div>
    <div class="page-sub">Manage your personal information and preferences</div>
@endsection

@section('content')
@php
    $user = auth()->user();
    $routePrefix = request()->routeIs('admin.*') ? 'admin' : 'agent';
@endphp

@if (session('profile_saved'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
     class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    Profile updated successfully.
</div>
@endif

@if (session('password_saved'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
     class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    Password changed successfully.
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Avatar & Quick Info --}}
    <div class="card-ds text-center p-6">
        <div class="w-20 h-20 rounded-full bg-accent flex items-center justify-center mx-auto mb-4 text-white text-3xl font-semibold">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div class="font-semibold text-gray-900 text-lg">{{ $user->name }}</div>
        <div class="text-sm text-gray-400 mt-1">{{ $user->email }}</div>
        <div class="mt-2">
            <span class="badge-ds {{ $user->role === 'admin' ? 'open' : ($user->role === 'agent' ? 'inprog' : 'low') }}">
                {{ ucfirst($user->role) }}
            </span>
        </div>
        <div class="mt-4 text-xs text-gray-400">Member since {{ $user->created_at->format('M Y') }}</div>
    </div>

    {{-- Profile Edit Form --}}
    <div class="space-y-4 lg:col-span-2">

        {{-- Personal info --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Personal Information</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name"
                               value="{{ old('name', $user->name) }}"
                               class="form-input-ds @error('name') border-red-400 @enderror"
                               required>
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email"
                               value="{{ old('email', $user->email) }}"
                               class="form-input-ds @error('email') border-red-400 @enderror"
                               required>
                        @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-input-ds bg-gray-50" value="{{ ucfirst($user->role) }}" readonly>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-ds primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Password change --}}
        <div class="card-ds">
            <div class="card-hdr">
                <div class="card-title">Change Password</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route($routePrefix . '.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="current_password"
                                   class="form-input-ds pr-10 @error('current_password') border-red-400 @enderror"
                                   required>
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="w-4 h-4" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="password"
                                   class="form-input-ds pr-10 @error('password') border-red-400 @enderror"
                                   required>
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="w-4 h-4" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-input-ds" required>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-ds primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
