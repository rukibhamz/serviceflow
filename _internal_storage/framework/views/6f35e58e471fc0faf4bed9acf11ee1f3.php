<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo e(app(\App\Services\SettingService::class)->get('brand_name', config('app.name', 'ServiceFlow'))); ?> — Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" href="<?php echo e(app(\App\Services\SettingService::class)->faviconUrl()); ?>" type="image/x-icon">

    <link rel="stylesheet" href="<?php echo e(asset('css/design-system.css')); ?>">
    <style id="theme-vars"><?php echo $cssVars ?? ''; ?></style>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: { brand: '#1a4fa0', accent: '#f97316' }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="bg-gray-100">

<div class="app-shell" x-data="{ sidebarOpen: window.innerWidth > 768, profileOpen: false }"
     @resize.window="if (window.innerWidth > 768) sidebarOpen = true; else sidebarOpen = false;">

    
    <div class="topnav">
        <button @click="sidebarOpen = !sidebarOpen"
                class="md:hidden flex items-center justify-center w-8 h-8 rounded hover:bg-white/10 transition mr-2">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($appSettings['brand_logo'])): ?>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center">
                <img src="<?php echo e($brandLogoUrl); ?>" alt="<?php echo e($appSettings['brand_name'] ?? 'ServiceFlow'); ?>" class="h-7 max-w-[160px] object-contain">
            </a>
        <?php else: ?>
            <div class="logo"><div class="logo-dot"></div><?php echo e($appSettings['brand_name'] ?? config('app.name', 'ServiceFlow')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <span class="ml-3 text-xs font-semibold bg-white/20 text-white px-2 py-0.5 rounded">Admin</span>

        <div class="nav-sep"></div>

        
        <a href="<?php echo e(route('agent.dashboard')); ?>"
           class="text-white/70 hover:text-white text-xs mr-4 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Agent View
        </a>

        
        <div class="relative ml-auto" x-data>
            <button @click="profileOpen = !profileOpen; $event.stopPropagation()"
                    class="nav-avatar focus:outline-none">
                <?php echo e(strtoupper(substr(auth()->user()?->name ?? 'AD', 0, 2))); ?>

            </button>
            <div x-show="profileOpen" @click.outside="profileOpen = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50 text-sm"
                 style="display:none; top: calc(100% + 8px);">
                <div class="px-4 py-2 border-b border-gray-100">
                    <div class="font-semibold text-gray-900"><?php echo e(auth()->user()?->name); ?></div>
                    <div class="text-xs text-gray-400 truncate"><?php echo e(auth()->user()?->email); ?></div>
                </div>
                <a href="<?php echo e(route('admin.profile')); ?>" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50 text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    My Profile
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
        
        <div class="sidebar-shell"
             :class="sidebarOpen ? 'sidebar-expanded' : 'sidebar-collapsed'"
             :style="sidebarOpen ? 'width:220px' : 'width:60px'">

            
            <div x-show="!sidebarOpen"
                 style="display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:12px 0;width:60px;flex:1;overflow-y:auto;gap:4px;">
                <button @click="sidebarOpen = true" title="Expand"
                    style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;border:none;border-radius:12px;background:transparent;color:rgba(255,255,255,0.5);cursor:pointer;margin-bottom:8px;">
                    <svg width="20" height="20" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4l4 4-4 4"/></svg>
                </button>
                <?php
                    $iconItems = [
                        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard',  'title' => 'Dashboard',   'icon' => '<rect x="1" y="1" width="6" height="6" rx="1.5" stroke-width="1.4"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke-width="1.4"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke-width="1.4"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke-width="1.4"/>'],
                        ['route' => 'admin.teams',     'match' => 'admin.teams',      'title' => 'Teams',       'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="1.4"/><circle cx="9" cy="7" r="4" stroke-width="1.4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke-width="1.4"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-width="1.4"/>'],
                        ['route' => 'admin.tenants',   'match' => 'admin.tenants',    'title' => 'Tenants',     'icon' => '<rect x="2" y="3" width="12" height="10" rx="1.5" stroke-width="1.4"/><path d="M5 7h6M5 9h4" stroke-width="1.4" stroke-linecap="round"/>'],
                        ['route' => 'admin.settings.index', 'match' => 'admin.settings.*', 'title' => 'Settings', 'icon' => '<path d="M8 1.5l1.2 1.1 1.6-.2.6 1.5 1.5.6-.2 1.6L14.5 8l-1.1 1.2.2 1.6-1.5.6-.6 1.5-1.6-.2L8 14.5l-1.2-1.1-1.6.2-.6-1.5-1.5-.6.2-1.6L1.5 8l1.1-1.2-.2-1.6 1.5-.6.6-1.5 1.6.2L8 1.5z" stroke-width="1.1"/><circle cx="8" cy="8" r="2.2" stroke-width="1.1"/>'],
                        ['route' => 'agent.dashboard', 'match' => null,               'title' => 'Agent View',  'icon' => '<path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>'],
                    ];
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $iconItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $isActive = $item['match'] ? Request::routeIs($item['match']) : false; ?>
                <a href="<?php echo e(route($item['route'])); ?>" title="<?php echo e($item['title']); ?>"
                   style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:6px;background:<?php echo e($isActive ? 'rgba(255,255,255,0.1)' : 'transparent'); ?>;color:<?php echo e($isActive ? '#fff' : 'rgba(255,255,255,0.6)'); ?>;text-decoration:none;transition:background 0.15s;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><?php echo $item['icon']; ?></svg>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="sidebar-nav" x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-out duration-150 delay-75"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 style="padding-top:8px;">

                <div style="display:flex;align-items:center;padding:12px 8px 8px 14px;margin-bottom:2px;">
                    <span style="font-size:10px;font-weight:600;color:rgba(255,255,255,0.5);letter-spacing:0.8px;text-transform:uppercase;flex:1;">Administration</span>
                    <button @click="sidebarOpen = false" class="sidebar-collapse-btn" style="color:rgba(255,255,255,0.6);">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4l-4 4 4 4"/></svg>
                    </button>
                </div>

                <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.dashboard') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/></svg>
                    Dashboard
                </a>

                <div class="sidebar-section">Tickets</div>
                <a href="<?php echo e(route('admin.tickets.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.tickets.index') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="2" y="2" width="12" height="12" rx="2" stroke-width="1.2"/><path d="M5 5h6M5 8h4" stroke-width="1.2" stroke-linecap="round"/></svg>
                    All Tickets
                </a>
                <a href="<?php echo e(route('admin.tickets.kanban')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.tickets.kanban') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="1" y="2" width="4" height="12" rx="1" stroke-width="1.2"/><rect x="6" y="2" width="4" height="8" rx="1" stroke-width="1.2"/><rect x="11" y="2" width="4" height="10" rx="1" stroke-width="1.2"/></svg>
                    Kanban
                </a>

                <div class="sidebar-section">ITSM</div>
                <a href="<?php echo e(route('admin.changes.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.changes.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 8a6 6 0 1 0 12 0" stroke-width="1.2" stroke-linecap="round"/><path d="M14 5l-2 3-2-3" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Changes
                </a>
                <a href="<?php echo e(route('admin.problems.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.problems.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="6" stroke-width="1.2"/><path d="M8 5v4M8 11v.5" stroke-width="1.4" stroke-linecap="round"/></svg>
                    Problems
                </a>
                <a href="<?php echo e(route('admin.assets.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.assets.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="2" y="3" width="12" height="10" rx="1.5" stroke-width="1.2"/><path d="M5 7h6M5 9h4" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Assets
                </a>
                <a href="<?php echo e(route('admin.knowledge.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.knowledge.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M3 2h8l2 2v10H3V2z" stroke-width="1.2" stroke-linejoin="round"/><path d="M6 6h5M6 9h4M6 12h3" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Knowledge Base
                </a>

                <div class="sidebar-section">People</div>
                <a href="<?php echo e(route('admin.users')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.users') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="1.4"/></svg>
                    Users
                </a>
                <a href="<?php echo e(route('admin.teams')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.teams') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="1.4"/><circle cx="9" cy="7" r="4" stroke-width="1.4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-width="1.4"/></svg>
                    Teams
                </a>
                <a href="<?php echo e(route('admin.tenants')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.tenants') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="2" y="3" width="12" height="10" rx="1.5" stroke-width="1.2"/><path d="M5 7h6M5 9h4" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Tenants
                </a>

                <div class="sidebar-section">Service Management</div>
                <a href="<?php echo e(route('admin.sla')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.sla') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="6" stroke-width="1.2"/><path d="M8 4v4l3 2" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    SLA Policies
                </a>

                <div class="sidebar-section">Analytics</div>
                <a href="<?php echo e(route('admin.automation.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.automation.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 8l3-5 3 3 3-5 3 7" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Automation
                </a>
                <a href="<?php echo e(route('admin.reports.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.reports.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 14V8l3-3 3 3 3-4 3 2" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Reports
                </a>

                <div class="sidebar-section">Configuration</div>
                <a href="<?php echo e(route('admin.settings.index')); ?>" class="sidebar-item <?php echo e(Request::routeIs('admin.settings.*') ? 'active' : ''); ?>">
                    <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M8 1.5l1.2 1.1 1.6-.2.6 1.5 1.5.6-.2 1.6L14.5 8l-1.1 1.2.2 1.6-1.5.6-.6 1.5-1.6-.2L8 14.5l-1.2-1.1-1.6.2-.6-1.5-1.5-.6.2-1.6L1.5 8l1.1-1.2-.2-1.6 1.5-.6.6-1.5 1.6.2L8 1.5z" stroke-width="1.1"/><circle cx="8" cy="8" r="2.2" stroke-width="1.1"/></svg>
                    Settings & SSO
                </a>

                <div class="sidebar-section">Navigation</div>
                <a href="<?php echo e(route('agent.dashboard')); ?>" class="sidebar-item">
                    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Agent View
                </a>
            </div>
        </div>

        
        <div class="main-content">
            <div class="page-header">
                <?php if (! empty(trim($__env->yieldContent('page-header')))): ?>
                    <?php echo $__env->yieldContent('page-header'); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="page-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($slot)): ?>
                    <?php echo e($slot); ?>

                <?php else: ?>
                    <?php echo $__env->yieldContent('content'); ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

<script>
    (function() {
        var s = document.querySelector('script[data-update-uri]');
        if (s) {
            var uri = s.getAttribute('data-update-uri');
            if (uri && uri.indexOf('/serviceflow') !== 0) {
                s.setAttribute('data-update-uri', '/serviceflow' + uri);
            }
        }
        if (window.livewireScriptConfig) {
            window.livewireScriptConfig.uri = '/serviceflow/livewire/update';
        }
    })();
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/layouts/admin.blade.php ENDPATH**/ ?>