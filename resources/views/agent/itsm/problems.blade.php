@extends('layouts.agent')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Problem Management</div>
            <div class="page-sub">Identify recurring incidents and manage root cause analysis</div>
        </div>
        <button class="btn-ds primary">+ New Problem Record</button>
    </div>
@endsection

@section('content')
<div class="card-ds">
    <div class="card-hdr">
        <div class="card-title">Problem Records</div>
        <span class="text-xs text-gray-400">Coming in Phase 2</span>
    </div>
    <div class="card-body py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="text-gray-400 font-medium mb-1">Problem Management is coming soon</div>
        <div class="text-xs text-gray-300">Recurring incident detection, root cause analysis, and KEDB will be available in Phase 2.</div>
    </div>
</div>
@endsection
