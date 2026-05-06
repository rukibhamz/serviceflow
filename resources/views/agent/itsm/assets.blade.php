@extends('layouts.agent')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">IT Asset Management</div>
            <div class="page-sub">Track hardware, software, and infrastructure assets</div>
        </div>
    </div>
@endsection

@section('content')
    <livewire:asset.asset-list />
@endsection
