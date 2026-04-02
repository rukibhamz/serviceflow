@extends('layouts.agent')

@section('page-header')
    <div class="page-title">Dashboard</div>
    <div class="page-sub">Welcome back, {{ auth()->user()->name }}</div>
@endsection

@section('content')
    <livewire:dashboard.dashboard-widgets />
@endsection
