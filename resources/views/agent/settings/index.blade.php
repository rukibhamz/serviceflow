@extends('layouts.agent')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Settings</h2>
        <p class="text-sm text-gray-500 mt-1">Configure your ServiceFlow instance</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach([
            ['SLA Policies', 'Define response and resolution time targets', 'agent.settings.index'],
            ['Teams & Agents', 'Manage team structure and agent assignments', 'agent.settings.index'],
            ['Email Settings', 'Configure inbound and outbound email', 'agent.settings.index'],
            ['Notifications', 'Set up alert and notification preferences', 'agent.settings.index'],
            ['Service Catalogue', 'Manage service catalogue items', 'agent.settings.index'],
            ['Working Hours', 'Define business hours for SLA calculations', 'agent.settings.index'],
        ] as [$title, $desc, $route])
            <a href="{{ route($route) }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow block">
                <p class="font-semibold text-gray-800 text-sm">{{ $title }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $desc }}</p>
            </a>
        @endforeach
    </div>
@endsection
