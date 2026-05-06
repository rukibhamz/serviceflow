@extends('layouts.admin')

@section('page-header')
    <div class="page-title">Admin Dashboard</div>
    <div class="page-sub">System overview — {{ now()->format('D d M Y') }}</div>
@endsection

@section('content')
    <livewire:admin.admin-dashboard />
@endsection
