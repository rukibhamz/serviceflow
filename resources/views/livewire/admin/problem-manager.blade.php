<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Problem Management</div>
            <div class="page-sub">Identify recurring incidents and manage root cause analysis</div>
        </div>
        <a href="{{ route('admin.tickets.create') }}?type=problem" class="btn-ds primary">+ New Problem</a>
    </div>

    <div class="stats-ds mb-2">
        <div class="stat-card"><div class="stat-label">Total Problems</div><div class="stat-val text-brand">{{ $stats['total'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Open</div><div class="stat-val text-red-500">{{ $stats['open'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Known Errors (KEDB)</div><div class="stat-val text-orange-500">{{ $stats['known_errors'] }}</div></div>
    </div>

    <livewire:problem.problem-list />
</div>
