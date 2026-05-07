@extends('layouts.manager')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold text-gray-900">Teams Overview</h1>
    <div class="rounded-lg border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr><th class="px-4 py-2 text-left">Team</th><th class="px-4 py-2 text-left">Lead</th><th class="px-4 py-2 text-left">Members</th><th class="px-4 py-2 text-left">Description</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($teams as $team)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $team->name }}</td>
                        <td class="px-4 py-2">{{ $team->lead?->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $team->members->count() }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $team->description ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No teams yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

