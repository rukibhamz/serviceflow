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

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="bg-gray-100">

    <div class="app-shell" x-data="{ sidebarOpen: true, profileOpen: false }">
        <!-- ── Top nav ── -->
        <div class="topnav">
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
            <div class="sidebar-shell" :class="sidebarOpen ? 'sidebar-expanded' : 'sidebar-collapsed'"
                 :style="sidebarOpen ? 'width:220px' : 'width:60px'">

                
                <div x-show="!sidebarOpen"
                     style="display:flex; flex-direction:column; align-items:center; justify-content:space-evenly; padding:12px 0; width:60px; flex:1; overflow-y:auto; overflow-x:hidden;">
                    
                    <button @click="sidebarOpen = true" title="Expand menu"
                        style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;border:none;border-radius:12px;background:transparent;color:rgba(255,255,255,0.5);cursor:pointer;transition:background 0.15s,transform 0.12s;flex-shrink:0;"
                        onmouseover="this.style.background='rgba(255,255,255,0.12)';this.style.transform='scale(1.15)'"
                        onmouseout="this.style.background='transparent';this.style.transform='scale(1)'">
                        <svg width="20" height="20" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4l4 4-4 4"/></svg>
                    </button>
                    
                    <?php
                        $stripItems = [
                            ['route' => 'agent.dashboard',     'match' => 'agent.dashboard',    'title' => 'Dashboard',       'icon' => '<rect x="1" y="1" width="6" height="6" rx="1.5" stroke-width="1.4"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke-width="1.4"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke-width="1.4"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke-width="1.4"/>'],
                            ['route' => 'agent.tickets.triage','match' => 'agent.tickets.triage','title' => 'My Queue',        'icon' => '<path d="M2 4h12M2 8h8M2 12h10" stroke-width="1.6" stroke-linecap="round"/>'],
                            ['route' => 'agent.tickets.index', 'match' => 'agent.tickets.index', 'title' => 'All Tickets',     'icon' => '<rect x="2" y="2" width="12" height="12" rx="2" stroke-width="1.4"/><path d="M5 5h6M5 8h4" stroke-width="1.4" stroke-linecap="round"/>'],
                            ['route' => 'agent.tickets.kanban','match' => 'agent.tickets.kanban','title' => 'Kanban',          'icon' => '<rect x="1" y="2" width="4" height="12" rx="1" stroke-width="1.4"/><rect x="6" y="2" width="4" height="8" rx="1" stroke-width="1.4"/><rect x="11" y="2" width="4" height="10" rx="1" stroke-width="1.4"/>'],
                            ['route' => 'agent.changes.index', 'match' => 'agent.changes.*',    'title' => 'Changes',         'icon' => '<path d="M2 8a6 6 0 1 0 12 0" stroke-width="1.4" stroke-linecap="round"/><path d="M14 5l-2 3-2-3" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>'],
                            ['route' => 'agent.problems.index','match' => 'agent.problems.*',   'title' => 'Problems',        'icon' => '<circle cx="8" cy="8" r="6" stroke-width="1.4"/><path d="M8 5v4M8 11v.5" stroke-width="1.6" stroke-linecap="round"/>'],
                            ['route' => 'agent.assets.index',  'match' => 'agent.assets.*',     'title' => 'Assets',          'icon' => '<rect x="2" y="3" width="12" height="10" rx="1.5" stroke-width="1.4"/><path d="M5 7h6M5 9h4" stroke-width="1.4" stroke-linecap="round"/>'],
                            ['route' => 'portal.index',        'match' => null,                 'title' => 'Portal',          'icon' => '<circle cx="8" cy="8" r="6" stroke-width="1.4"/><path d="M8 2v12M2 8h12" stroke-width="1.4"/>'],
                            ['route' => 'agent.knowledge.index','match' => 'agent.knowledge.*', 'title' => 'Knowledge Base',  'icon' => '<path d="M3 2h8l2 2v10H3V2z" stroke-width="1.4" stroke-linejoin="round"/><path d="M6 6h5M6 9h4M6 12h3" stroke-width="1.4" stroke-linecap="round"/>'],
                            ['route' => 'agent.automation.index','match' => 'agent.automation.*','title' => 'Automation',     'icon' => '<path d="M2 8l3-5 3 3 3-5 3 7" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>'],
                            ['route' => 'agent.reports.index', 'match' => 'agent.reports.*',    'title' => 'Reports',         'icon' => '<path d="M2 14V8l3-3 3 3 3-4 3 2" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>'],
                            ['route' => 'agent.settings.index','match' => 'agent.settings.index','title' => 'Settings',       'icon' => '<circle cx="8" cy="8" r="2.5" stroke-width="1.4"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.05 3.05l1.41 1.41M11.54 11.54l1.41 1.41M3.05 12.95l1.41-1.41M11.54 4.46l1.41-1.41" stroke-width="1.4" stroke-linecap="round"/>'],
                            ['route' => 'agent.profile',       'match' => 'agent.profile',      'title' => 'Profile',         'icon' => '<circle cx="8" cy="5" r="3" stroke-width="1.4"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke-width="1.4" stroke-linecap="round"/>'],
                        ];
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stripItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isActive = $item['match'] ? Request::routeIs($item['match']) : false;
                            $bg = $isActive ? 'var(--brand)' : 'transparent';
                            $color = $isActive ? '#fff' : 'rgba(255,255,255,0.5)';
                        ?>
                        <a href="<?php echo e(route($item['route'])); ?>" title="<?php echo e($item['title']); ?>"
                           style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px;background:<?php echo e($bg); ?>;color:<?php echo e($color); ?>;text-decoration:none;transition:background 0.15s,transform 0.12s;flex-shrink:0;"
                           onmouseover="if(this.dataset.active!='1'){this.style.background='rgba(255,255,255,0.12)';this.style.color='#fff';}this.style.transform='scale(1.15)'"
                           onmouseout="if(this.dataset.active!='1'){this.style.background='<?php echo e($bg); ?>';this.style.color='<?php echo e($color); ?>';}this.style.transform='scale(1)'"
                           data-active="<?php echo e($isActive ? '1' : '0'); ?>">
                            <svg width="20" height="20" viewBox="0 0 16 16" fill="none" stroke="currentColor"><?php echo $item['icon']; ?></svg>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="sidebar-nav" x-show="sidebarOpen"
                     x-transition:enter="transition-opacity ease-out duration-150 delay-75"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     style="padding-top: 8px;">

                    
                    <div style="display:flex; align-items:center; padding: 12px 8px 8px 14px; margin-bottom: 2px;">
                        <span style="font-size:10px; font-weight:500; color:var(--color-text-tertiary); letter-spacing:0.5px; text-transform:uppercase; flex:1;">Service Desk</span>
                        <button @click="sidebarOpen = false" class="sidebar-collapse-btn" title="Collapse menu" style="margin-left:8px;">
                            
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 4l-4 4 4 4"/>
                            </svg>
                        </button>
                    </div>
                    <a href="<?php echo e(route('agent.dashboard')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.dashboard') ? 'active' : ''); ?>">
                        <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><rect x="1" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="1" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="1" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/><rect x="9" y="9" width="6" height="6" rx="1.5" stroke-width="1.2"/></svg>
                        Dashboard
                    </a>
                    <a href="<?php echo e(route('agent.tickets.triage')); ?>" class="sidebar-item <?php echo e(Request::routeIs('agent.tickets.triage') ? 'active' : ''); ?>">
                        <svg class="sidebar-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor"><path d="M2 4h12M2 8h8M2 12h10" stroke-width="1.2" stroke-linecap="round"/></svg>
                        My Queue
                        <?php $myQueueCount = \App\Models\Ticket::whereNull('assignee_id')->whereNotIn('status', ['resolved','closed'])->count(); ?>
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

    <script>
        // Patch Livewire update URI for XAMPP subdirectory (/serviceflow)
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
<?php /**PATH C:\xampp\htdocs\serviceflow\resources\views/layouts/agent.blade.php ENDPATH**/ ?>