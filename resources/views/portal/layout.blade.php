<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Support Portal') — ServiceFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <nav class="border-b bg-white px-6 py-3 flex items-center justify-between">
        <a href="{{ route('portal.index') }}" class="font-semibold text-blue-600">Support Portal</a>
        <div class="flex items-center gap-4 text-sm">
            @auth
                <a href="{{ route('portal.tickets.index') }}" class="text-gray-600 hover:text-gray-900">My Tickets</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-gray-500 hover:text-gray-900">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Sign in</a>
            @endauth
        </div>
    </nav>

    <main class="mx-auto max-w-4xl px-4 py-8">
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
