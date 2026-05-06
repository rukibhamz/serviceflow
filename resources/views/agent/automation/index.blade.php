@extends('layouts.agent')

@section('page-header')
    <div class="page-title">Automation Rules</div>
    <div class="page-sub">Define triggers and actions to automate your workflow</div>
@endsection

@section('content')
    <livewire:automation.automation-builder />
@endsection
