@extends('layouts.team-lead')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold text-gray-900">My Teams</h1>
    <div class="space-y-4">
        @forelse($teams as $team)
            <div class="rounded-lg border bg-white p-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ $team->name }}</h2>
                    <span class="text-xs text-gray-500">{{ $team->members->count() }} members</span>
                </div>
                <p class="mt-1 text-sm text-gray-500">{{ $team->description ?: 'No description' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse($team->members as $member)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">{{ $member->name }}</span>
                    @empty
                        <span class="text-xs text-gray-400">No members assigned.</span>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="rounded-lg border bg-white p-6 text-sm text-gray-500">No teams assigned to you as Team Lead yet.</div>
        @endforelse
    </div>
</div>
@endsection

