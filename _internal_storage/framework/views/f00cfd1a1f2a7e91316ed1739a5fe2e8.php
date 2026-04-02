<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo e(config('app.name', 'ServiceFlow')); ?> — Agent</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/design-system.css')); ?>">
    
    <style id="theme-vars"><?php echo $cssVars ?? ''; ?></style>
    
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
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="bg-gray-100">

    <div class="app-shell" x-data="{ sidebarOpen: true, profileOpen: false }">
        <!-- ── Top nav ── -->
        <div class="topnav">
            
            <button @click="sidebarOpen = !sidebarOpen" class="flex items-center justify-center w-8 h-8 rounded hover:bg-white/10 transition mr-2" title="Toggle menu">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($appSettings['brand_logo'])): ?>
                <a href="<?php echo e(route('agent.dashboard')); ?>" class="flex items-center">
                    <img src="<?php echo e($brandLogoUrl); ?>" alt="<?php echo e($appSettings['brand_name'] ?? 'ServiceFlow'); ?>" class="h-7 max-w-[160px] object-contain">
                </a>
            <?php else: ?>
                <div class="logo"><div class="logo-dot"></div><?php echo e($appSettings['brand_name'] ?? config('app.name', 'ServiceFlow')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="nav-sep"></div>

            
            <div class="relative ml-auto" x-data>
                <button 
                    @click="profileOpen = !profileOpen; $event.stopPropagation()" 
                    class="nav-avatar focus:outline-none focus:ring-2 focus:ring-white/40 transition"
                    title="<?php echo e(auth()->user()?->name); ?>"
                ><?php echo e(strtoupper(substr(auth()->user()?->name ?? 'GU', 0, 2))); ?></button>

                
                <div 
                    x-show="profileOpen" 
                    @click.outside="profileOpen = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50 text-sm"
                    style="display: none; top: calc(100% + 8px);"
                >
                    <div class="px-4 py-2 border-b border-gray-100">
                        <div class="font-semibold text-gray-900"><?php echo e(auth()->user()?->name); ?></div>
                        <div class="text-xs text-gray-400 truncate"><?php echo e(auth()->user()?->email); ?></div>
                    </div>
                    <a href="<?php echo e(route('agent.profile')); ?>" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        My Profile
                    </a>
                    <a href="<?php echo e(route('agent.settings.index')); ?>" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </a>
                    <div class="border-t border-gray-100 mt-1 pt-1">
                        <a href="<?php echo e(route('logout')); ?>" class="flex items-center gap-2 px-4 py-2 hover:bg-red-50 text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="body-wrapper">
            <!-- ── Sidebar ── -->
            <div class="sidebar" x-show="sidebarOpen" x-transition:enter="transition-all ease-out duration-200" x-transition:leave="transition-all ease-in duration-150" style="min-height:0;">
                <div class="sidebar-section">Service Desk</div>
                <a href="<?php echo e(route('agent.dashboard')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.dashboard') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/></svg>
                    Dashboard
                </a>
                <a href="<?php echo e(route('agent.tickets.triage')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.tickets.triage') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 4h12M2 8h8M2 12h10" stroke-width="1.2" stroke-linecap="round"/></svg>
                    My Queue
                    <?php $myQueueCount = \App\Models\Ticket::where('assignee_id', auth()->id())->whereNotIn('status', ['resolved','closed'])->count(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($myQueueCount > 0): ?>
                        <span class="sidebar-badge"><?php echo e($myQueueCount); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
                <a href="<?php echo e(route('agent.tickets.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.tickets.index') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="2" y="2" width="12" height="12" rx="2" stroke-width="1.2"/><path d="M5 5h6M5 8h4" stroke-width="1.2" stroke-linecap="round"/></svg>
                    All Tickets
                </a>
                <a href="<?php echo e(route('agent.tickets.kanban')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.tickets.kanban') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="1" y="2" width="4" height="12" rx="1" stroke-width="1.2"/><rect x="6" y="2" width="4" height="8" rx="1" stroke-width="1.2"/><rect x="11" y="2" width="4" height="10" rx="1" stroke-width="1.2"/></svg>
                    Kanban
                </a>

                <div class="sidebar-section">ITSM</div>
                <a href="<?php echo e(route('agent.changes.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.changes.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 8a6 6 0 1 0 12 0" stroke-width="1.2" stroke-linecap="round"/><path d="M14 5l-2 3-2-3" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Changes
                </a>
                <a href="<?php echo e(route('agent.problems.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.problems.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="6" stroke-width="1.2"/><path d="M8 5v4M8 11v.5" stroke-width="1.4" stroke-linecap="round"/></svg>
                    Problems
                </a>
                <a href="<?php echo e(route('agent.assets.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.assets.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="2" y="3" width="12" height="10" rx="1.5" stroke-width="1.2"/><path d="M5 7h6M5 9h4" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Assets
                </a>

                <div class="sidebar-section">Self-Service</div>
                <a href="<?php echo e(route('portal.index')); ?>" class="sidebar-item">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="6" stroke-width="1.2"/><path d="M8 2v12M2 8h12" stroke-width="1.2"/></svg>
                    Portal
                </a>
                <a href="<?php echo e(route('agent.knowledge.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.knowledge.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M3 2h8l2 2v10H3V2z" stroke-width="1.2" stroke-linejoin="round"/><path d="M6 6h5M6 9h4M6 12h3" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Knowledge Base
                </a>

                <div class="sidebar-section">Automation</div>
                <a href="<?php echo e(route('agent.automation.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.automation.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 8l3-5 3 3 3-5 3 7" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Automation Rules
                </a>
                <a href="<?php echo e(route('agent.reports.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.reports.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 14V8l3-3 3 3 3-4 3 2" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Reports
                </a>

                <div class="sidebar-section">Account</div>
                <a href="<?php echo e(route('agent.settings.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.settings.index') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="2.5" stroke-width="1.2"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.05 3.05l1.41 1.41M11.54 11.54l1.41 1.41M3.05 12.95l1.41-1.41M11.54 4.46l1.41-1.41" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Settings
                </a>
                <a href="<?php echo e(route('agent.profile')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.profile') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="5" r="3" stroke-width="1.2"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Profile
                </a>
            </div>

            <!-- ── Main content ── -->
            <div class="main-content">
                <div class="page-header">
                    <?php echo $__env->yieldContent('page-header'); ?>
                </div>
                <div class="page-body">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/layouts/agent.blade.php ENDPATH**/ ?>