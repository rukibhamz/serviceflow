@extends('layouts.agent')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Tickets</h2>
    </div>
    <livewire:tickets.ticket-list-component />
@endsection
