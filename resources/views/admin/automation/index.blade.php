@extends('layouts.admin')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">Automation Rules</div>
            <div class="page-sub">Define triggers and actions to automate your workflow</div>
        </div>
        <a href="{{ route('admin.automation.index', ['new' => 1]) }}" class="btn-ds primary">+ New Automation</a>
    </div>
@endsection

@section('content')
    <livewire:automation.automation-builder />
@endsection
