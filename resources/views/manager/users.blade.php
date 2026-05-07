@extends('layouts.manager')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-bold text-gray-900">Users Overview</h1>
    <div class="rounded-lg border bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr><th class="px-4 py-2 text-left">Name</th><th class="px-4 py-2 text-left">Email</th><th class="px-4 py-2 text-left">Role</th><th class="px-4 py-2 text-left">Teams</th><th class="px-4 py-2 text-left">Status</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'end_user')) }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $user->teams->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-2">{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $users->links() }}</div>
</div>
@endsection

