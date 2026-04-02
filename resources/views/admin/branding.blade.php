@extends('layouts.agent')

@section('page-header')
    <div class="page-title">Branding & Theme</div>
    <div class="page-sub">Customize the company name, logo, and color theme</div>
@endsection

@section('content')
    <livewire:admin.branding-settings />
@endsection
