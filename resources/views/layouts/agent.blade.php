<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'ServiceFlow') }} — Agent</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 items-center justify-between">
                <div class="flex items-center gap-6">
                    <span class="text-lg font-bold text-blue-600">ServiceFlow</span>
                    <a href="{{ route('agent.tickets.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Tickets</a>
                    <a href="{{ route('agent.tickets.kanban') }}" class="text-sm text-gray-600 hover:text-gray-900">Kanban Board</a>
                    <a href="{{ route('agent.tickets.create') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Create Ticket</a>
                    <a href="{{ route('agent.tickets.triage') }}" class="text-sm text-gray-600 hover:text-gray-900">Triage Queue</a>
                </div>
                <div class="text-sm text-gray-500">
                    {{ auth()->user()?->name ?? 'Guest' }}
                </div>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
