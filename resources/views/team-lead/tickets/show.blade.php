@extends('layouts.team-lead')

@section('content')
    <div class="mb-4">
        <a href="{{ route('team-lead.tickets') }}" class="text-sm text-blue-600 hover:underline">&larr; Back to team tickets</a>
    </div>
    <livewire:tickets.ticket-resource :ticket="$ticket" />
@endsection

