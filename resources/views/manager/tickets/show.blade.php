@extends('layouts.manager')

@section('content')
    <div class="mb-4">
        <a href="{{ route('manager.tickets') }}" class="text-sm text-blue-600 hover:underline">&larr; Back to manager tickets</a>
    </div>
    <livewire:tickets.ticket-resource :ticket="$ticket" />
@endsection

