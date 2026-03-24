<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'ServiceFlow') }} — Agent</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { 
            theme: { 
                extend: { 
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        brand: '#1a4fa0',
                        accent: '#f97316'
                    }
                } 
            } 
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">

    <div class="app-shell">
        <!-- ── Top nav ── -->
        <div class="topnav">
            <div class="logo"><div class="logo-dot"></div>ServiceFlow</div>
            <div class="nav-sep"></div>
            <a href="{{ route('agent.dashboard') }}" class="nav-pill {{ Request::routeIs('agent.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('agent.tickets.index') }}" class="nav-pill {{ Request::routeIs('agent.tickets.*') ? 'active' : '' }}">Queue</a>
            <a href="{{ route('portal.index') }}" class="nav-pill">Portal</a>
            <a href="{{ route('agent.settings.index') }}" class="nav-pill {{ Request::routeIs('agent.settings.*') ? 'active' : '' }}">Settings</a>
            <div class="nav-avatar" title="{{ auth()->user()?->name }}">{{ strtoupper(substr(auth()->user()?->name ?? 'GU', 0, 2)) }}</div>
            <a href="{{ route('logout') }}" class="text-xs text-white/60 hover:text-white ml-2">Logout</a>
        </div>

        <div class="body-wrapper">
            <!-- ── Sidebar ── -->
            <div class="sidebar">
                <div class="sidebar-section">Service Desk</div>
                <a href="{{ route('agent.dashboard') }}" class="sidebar-item {{ Request::routeIs('agent.dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('agent.tickets.triage') }}" class="sidebar-item {{ Request::routeIs('agent.tickets.triage') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 4h12M2 8h8M2 12h10" stroke-width="1.2" stroke-linecap="round"/></svg>
                    My Queue
                    @php $myQueueCount = \App\Models\Ticket::where('assignee_id', auth()->id())->whereNotIn('status', ['resolved','closed'])->count(); @endphp
                    @if($myQueueCount > 0)
                        <span class="sidebar-badge">{{ $myQueueCount }}</span>
                    @endif
                </a>
                <a href="{{ route('agent.tickets.index') }}" class="sidebar-item {{ Request::routeIs('agent.tickets.index') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="2" y="2" width="12" height="12" rx="2" stroke-width="1.2"/><path d="M5 5h6M5 8h4" stroke-width="1.2" stroke-linecap="round"/></svg>
                    All Tickets
                </a>
                <a href="{{ route('agent.tickets.kanban') }}" class="sidebar-item {{ Request::routeIs('agent.tickets.kanban') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="1" y="2" width="4" height="12" rx="1" stroke-width="1.2"/><rect x="6" y="2" width="4" height="8" rx="1" stroke-width="1.2"/><rect x="11" y="2" width="4" height="10" rx="1" stroke-width="1.2"/></svg>
                    Kanban
                </a>

                <div class="sidebar-section">ITSM</div>
                <a href="{{ route('agent.changes.index') }}" class="sidebar-item {{ Request::routeIs('agent.changes.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 8a6 6 0 1 0 12 0" stroke-width="1.2" stroke-linecap="round"/><path d="M14 5l-2 3-2-3" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Changes
                </a>
                <a href="{{ route('agent.problems.index') }}" class="sidebar-item {{ Request::routeIs('agent.problems.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="6" stroke-width="1.2"/><path d="M8 5v4M8 11v.5" stroke-width="1.4" stroke-linecap="round"/></svg>
                    Problems
                </a>
                <a href="{{ route('agent.assets.index') }}" class="sidebar-item {{ Request::routeIs('agent.assets.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="2" y="3" width="12" height="10" rx="1.5" stroke-width="1.2"/><path d="M5 7h6M5 9h4" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Assets
                </a>

                <div class="sidebar-section">Self-Service</div>
                <a href="{{ route('portal.index') }}" class="sidebar-item">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="6" stroke-width="1.2"/><path d="M8 2v12M2 8h12" stroke-width="1.2"/></svg>
                    Portal
                </a>
                <a href="{{ route('agent.knowledge.index') }}" class="sidebar-item {{ Request::routeIs('agent.knowledge.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M3 2h8l2 2v10H3V2z" stroke-width="1.2" stroke-linejoin="round"/><path d="M6 6h5M6 9h4M6 12h3" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Knowledge Base
                </a>

                <div class="sidebar-section">Automation</div>
                <a href="{{ route('agent.automation.index') }}" class="sidebar-item {{ Request::routeIs('agent.automation.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 8l3-5 3 3 3-5 3 7" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Automation Rules
                </a>
                <a href="{{ route('agent.reports.index') }}" class="sidebar-item {{ Request::routeIs('agent.reports.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 14V8l3-3 3 3 3-4 3 2" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Reports
                </a>

                <div class="sidebar-section">Account</div>
                <a href="{{ route('agent.settings.index') }}" class="sidebar-item {{ Request::routeIs('agent.settings.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="2.5" stroke-width="1.2"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.05 3.05l1.41 1.41M11.54 11.54l1.41 1.41M3.05 12.95l1.41-1.41M11.54 4.46l1.41-1.41" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Settings
                </a>
                <a href="{{ route('agent.profile') }}" class="sidebar-item {{ Request::routeIs('agent.profile') ? 'active' : '' }}">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="5" r="3" stroke-width="1.2"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Profile
                </a>
            </div>

            <!-- ── Main content ── -->
            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
