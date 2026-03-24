@extends('layouts.agent')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
    </div>
    <livewire:dashboard.dashboard-widgets />
@endsection
