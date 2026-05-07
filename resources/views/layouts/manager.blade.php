<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'ServiceFlow') }} — Manager</title>
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
<div class="min-h-screen">
    <header class="bg-indigo-900 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a href="{{ route('manager.dashboard') }}" class="text-sm font-semibold tracking-wide">Manager Portal</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('manager.dashboard') }}" class="hover:underline">Dashboard</a>
                <a href="{{ route('manager.teams') }}" class="hover:underline">Teams</a>
                <a href="{{ route('manager.users') }}" class="hover:underline">Users</a>
                <a href="{{ route('manager.tickets') }}" class="hover:underline">Tickets</a>
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

