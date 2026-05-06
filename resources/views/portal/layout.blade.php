<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Support Portal') — ServiceFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ app(\App\Services\SettingService::class)->faviconUrl() }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'sans-serif'] } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <nav class="border-b bg-white px-6 py-3 flex items-center justify-between">
        <a href="{{ route('portal.index') }}" class="font-semibold text-blue-600">Support Portal</a>
        <div class="flex items-center gap-4 text-sm">
            @auth
                <a href="{{ route('portal.index') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                <a href="{{ route('portal.tickets.index') }}" class="text-gray-600 hover:text-gray-900">My Tickets</a>
                <a href="{{ route('logout') }}" class="text-gray-500 hover:text-gray-900">Sign out</a>
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
