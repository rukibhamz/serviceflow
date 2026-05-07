<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'ServiceFlow') }} — Team Lead</title>
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
<div class="min-h-screen">
    <header class="bg-slate-800 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a href="{{ route('team-lead.dashboard') }}" class="text-sm font-semibold tracking-wide">Team Lead Portal</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('team-lead.dashboard') }}" class="hover:underline">Dashboard</a>
                <a href="{{ route('team-lead.teams') }}" class="hover:underline">My Teams</a>
                <a href="{{ route('team-lead.tickets') }}" class="hover:underline">Team Tickets</a>
                <a href="{{ route('team-lead.knowledge.index') }}" class="hover:underline">Knowledge</a>
                <a href="{{ route('team-lead.service-catalogue.index') }}" class="hover:underline">Service Catalogue</a>
                <a href="{{ route('logout') }}" class="hover:underline">Logout</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6">
        @yield('content')
    </main>
</div>
@livewireScripts
</body>
</html>

