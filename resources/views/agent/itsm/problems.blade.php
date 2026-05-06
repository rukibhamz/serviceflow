@extends('layouts.agent')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Problem Management</div>
            <div class="page-sub">Identify recurring incidents and manage root cause analysis</div>
        </div>
    </div>
@endsection

@section('content')
@php
    $total      = \App\Models\Ticket::where('type','problem')->count();
    $open       = \App\Models\Ticket::where('type','problem')->whereNotIn('status',['resolved','closed'])->count();
    $knownErrors= \App\Models\Ticket::where('type','problem')->whereJsonContains('custom_fields->known_error', true)->count();
@endphp

<div class="stats-ds mb-4">
    <div class="stat-card">
        <div class="stat-label">Total Problems</div>
        <div class="stat-val text-brand">{{ $total }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Open</div>
        <div class="stat-val text-red-500">{{ $open }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Known Errors (KEDB)</div>
        <div class="stat-val text-orange-500">{{ $knownErrors }}</div>
    </div>
</div>

<livewire:problem.problem-list />
@endsection
