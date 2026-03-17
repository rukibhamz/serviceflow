@extends('layouts.agent')

@section('content')
    <div class="mb-4">
        <a href="{{ route('agent.tickets.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Back to tickets</a>
    </div>
    <livewire:tickets.ticket-resource :ticket="$ticket" />
@endsection
