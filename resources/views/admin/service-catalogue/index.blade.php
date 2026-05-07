@extends('layouts.admin')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Service Catalogue</h1>
        <a href="{{ route('admin.service-catalogue.create') }}" class="btn-ds primary">+ New Item</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2">Priority</th>
                <th class="px-4 py-2">Team</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-2">{{ $item->name }}</td>
                    <td class="px-4 py-2">{{ $item->type }}</td>
                    <td class="px-4 py-2">{{ $item->priority }}</td>
                    <td class="px-4 py-2">{{ $item->team?->name ?? 'All teams' }}</td>
                    <td class="px-4 py-2">{{ $item->is_active ? 'Active' : 'Inactive' }}</td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.service-catalogue.edit', $item) }}" class="btn-ds ghost">Edit</a>
                            <form method="POST" action="{{ route('admin.service-catalogue.toggle', $item) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-ds ghost">{{ $item->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.service-catalogue.destroy', $item) }}" onsubmit="return confirm('Delete this catalogue item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ds danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-slate-500">No custom service catalogue items yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection

