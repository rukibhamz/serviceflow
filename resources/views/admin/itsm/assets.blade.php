@extends('layouts.admin')

@section('page-header')
    <div class="page-title">IT Asset Management</div>
    <div class="page-sub">Track hardware, software, and infrastructure assets</div>
@endsection

@section('content')
    <livewire:asset.asset-list />
@endsection
