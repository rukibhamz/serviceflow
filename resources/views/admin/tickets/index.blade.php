@extends('layouts.admin')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <div class="page-title">All Tickets</div>
            <div class="page-sub">System-wide ticket management</div>
        </div>
        <a href="{{ route('admin.tickets.create') }}" class="btn-ds primary">+ New Ticket</a>
    </div>
@endsection

@section('content')
    <livewire:tickets.ticket-list-component />
@endsection
