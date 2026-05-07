<?php

use App\Http\Controllers\InstallerController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    // Redirect to installer if app has not been installed yet
    if (env('APP_INSTALLED') !== 'true' && ! file_exists(storage_path('install.lock'))) {
        return redirect()->route('installer.index');
    }

    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('team_lead') || $user->role === 'team_lead') {
            return redirect()->route('team-lead.dashboard');
        }
        if ($user->hasRole('manager') || $user->role === 'manager') {
            return redirect()->route('manager.dashboard');
        }
        if ($user->hasRole('agent') || $user->role === 'agent') {
            return redirect()->route('agent.dashboard');
        }
        return redirect()->route('portal.index');
    }
    return redirect()->route('login');
});

// Convenience redirects — /agent and /admin go straight to the right dashboard
Route::get('/agent', function () {
    if (! auth()->check()) return redirect()->route('login');
    $user = auth()->user();
    if ($user->hasRole('agent') || $user->role === 'agent' || $user->hasRole('admin') || $user->role === 'admin') {
        return redirect()->route('agent.dashboard');
    }
    return redirect()->route('portal.index');
})->middleware('auth');

Route::get('/admin', function () {
    if (! auth()->check()) return redirect()->route('login');
    $user = auth()->user();
    if ($user->hasRole('admin') || $user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('team_lead') || $user->role === 'team_lead') {
        return redirect()->route('team-lead.dashboard');
    }
    if ($user->hasRole('manager') || $user->role === 'manager') {
        return redirect()->route('manager.dashboard');
    }
    if ($user->hasRole('agent') || $user->role === 'agent') {
        return redirect()->route('agent.dashboard');
    }
    return redirect()->route('portal.index');
})->middleware('auth');

Route::get('/team-lead', function () {
    if (! auth()->check()) return redirect()->route('login');
    $user = auth()->user();
    if ($user->hasRole('team_lead') || $user->role === 'team_lead' || $user->hasRole('admin') || $user->role === 'admin') {
        return redirect()->route('team-lead.dashboard');
    }
    return redirect()->route('portal.index');
})->middleware('auth');

Route::get('/manager', function () {
    if (! auth()->check()) return redirect()->route('login');
    $user = auth()->user();
    if ($user->hasRole('manager') || $user->role === 'manager' || $user->hasRole('admin') || $user->role === 'admin') {
        return redirect()->route('manager.dashboard');
    }
    return redirect()->route('portal.index');
})->middleware('auth');

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'store'])->name('login.store')->middleware('guest');

// SSO routes
Route::middleware('guest')->prefix('auth')->name('auth.sso.')->group(function () {
    Route::get('/{provider}/redirect', [App\Http\Controllers\SocialAuthController::class, 'redirect'])->name('redirect');
    Route::get('/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])->name('callback');
});

Route::match(['GET', 'POST'], '/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('auth');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin'], 'as' => 'admin.'], function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    // People
    Route::get('/teams', \App\Livewire\Admin\TeamManager::class)->name('teams');
    Route::post('/teams', [App\Http\Controllers\AdminController::class, 'storeTeam'])->name('teams.store');
    Route::patch('/teams/{team}', [App\Http\Controllers\AdminController::class, 'updateTeam'])->name('teams.update');
    Route::post('/teams/{team}/members', [App\Http\Controllers\AdminController::class, 'updateTeamMembers'])->name('teams.members.update');
    Route::delete('/teams/{team}', [App\Http\Controllers\AdminController::class, 'destroyTeam'])->name('teams.destroy');
    Route::get('/tenants', \App\Livewire\Admin\TenantManager::class)->name('tenants');
    Route::post('/tenants', [App\Http\Controllers\AdminController::class, 'provisionTenant'])->name('tenants.provision');
    Route::patch('/tenants/{tenant}/suspend', [App\Http\Controllers\AdminController::class, 'suspendTenant'])->name('tenants.suspend');
    Route::patch('/tenants/{tenant}/activate', [App\Http\Controllers\AdminController::class, 'activateTenant'])->name('tenants.activate');
    Route::get('/users', \App\Livewire\Admin\UserManager::class)->name('users');
    Route::post('/users', [App\Http\Controllers\AdminController::class, 'storeUser'])->name('users.store');
    Route::patch('/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/status', [App\Http\Controllers\AdminController::class, 'toggleUserStatus'])->name('users.status.toggle');
    Route::post('/invitations', [App\Http\Controllers\AdminController::class, 'sendInvitation'])->name('invitations.send');
    Route::delete('/invitations/{invitation}', [App\Http\Controllers\AdminController::class, 'cancelInvitation'])->name('invitations.cancel');

    // SLA
    Route::get('/sla', \App\Livewire\Admin\SlaManager::class)->name('sla');
    Route::post('/sla', [App\Http\Controllers\AdminController::class, 'saveSlaPolicy'])->name('sla.save');
    Route::patch('/sla/{policy}/toggle', [App\Http\Controllers\AdminController::class, 'toggleSlaPolicy'])->name('sla.toggle');
    Route::delete('/sla/{policy}', [App\Http\Controllers\AdminController::class, 'deleteSlaPolicy'])->name('sla.delete');

    // Tickets
    Route::get('/tickets', fn () => view('admin.tickets.index'))->name('tickets.index');
    Route::post('/tickets', [App\Http\Controllers\TicketController::class, 'store'])->name('tickets.store');
    Route::patch('/tickets/{ticket}/status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::post('/tickets/{ticket}/comments', [App\Http\Controllers\WorkspaceActionController::class, 'addTicketComment'])->name('tickets.comments.add');
    Route::patch('/tickets/{ticket}/status/save', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketStatus'])->name('tickets.status.save');
    Route::patch('/tickets/{ticket}/priority', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketPriority'])->name('tickets.priority.save');
    Route::patch('/tickets/{ticket}/assignee', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketAssignee'])->name('tickets.assignee.save');
    Route::post('/tickets/{ticket}/merge', [App\Http\Controllers\WorkspaceActionController::class, 'mergeTicket'])->name('tickets.merge');
    Route::post('/tickets/{ticket}/watch', [App\Http\Controllers\WorkspaceActionController::class, 'toggleTicketWatch'])->name('tickets.watch.toggle');
    Route::get('/tickets/kanban', \App\Livewire\Tickets\TicketKanban::class)->name('tickets.kanban');
    Route::get('/tickets/create', \App\Livewire\Tickets\CreateTicket::class)->name('tickets.create');
    Route::get('/tickets/{ticket:ulid}', fn (\App\Models\Ticket $ticket) => view('admin.tickets.show', compact('ticket')))->name('tickets.show');

    // ITSM
    Route::get('/changes', \App\Livewire\Admin\ChangeManager::class)->name('changes.index');
    Route::get('/problems', \App\Livewire\Admin\ProblemManager::class)->name('problems.index');
    Route::post('/problems/{problem}/root-cause', [App\Http\Controllers\WorkspaceActionController::class, 'saveRootCause'])->name('problems.root-cause.save');
    Route::post('/problems/{problem}/incidents/{incident}/link', [App\Http\Controllers\WorkspaceActionController::class, 'linkIncident'])->name('problems.incidents.link');
    Route::delete('/problems/incidents/{incident}', [App\Http\Controllers\WorkspaceActionController::class, 'unlinkIncident'])->name('problems.incidents.unlink');
    Route::get('/assets', fn () => view('admin.itsm.assets'))->name('assets.index');
    Route::get('/service-catalogue', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'index'])
        ->defaults('portal', 'admin')
        ->name('service-catalogue.index');
    Route::get('/service-catalogue/create', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'create'])
        ->defaults('portal', 'admin')
        ->name('service-catalogue.create');
    Route::post('/service-catalogue', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'store'])
        ->defaults('portal', 'admin')
        ->name('service-catalogue.store');
    Route::get('/service-catalogue/{item}/edit', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'edit'])
        ->defaults('portal', 'admin')
        ->name('service-catalogue.edit');
    Route::patch('/service-catalogue/{item}', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'update'])
        ->defaults('portal', 'admin')
        ->name('service-catalogue.update');
    Route::patch('/service-catalogue/{item}/toggle', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'toggle'])
        ->defaults('portal', 'admin')
        ->name('service-catalogue.toggle');
    Route::delete('/service-catalogue/{item}', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'destroy'])
        ->defaults('portal', 'admin')
        ->name('service-catalogue.destroy');
    Route::post('/assets/save', [App\Http\Controllers\WorkspaceActionController::class, 'saveAsset'])->name('assets.save');
    Route::post('/assets/import', [App\Http\Controllers\WorkspaceActionController::class, 'importAssets'])->name('assets.import');
    Route::patch('/assets/{asset}/assign', [App\Http\Controllers\WorkspaceActionController::class, 'assignAsset'])->name('assets.assign');
    Route::delete('/assets/{asset}', [App\Http\Controllers\WorkspaceActionController::class, 'deleteAsset'])->name('assets.delete');
    Route::patch('/assets/{asset}/unassign', [App\Http\Controllers\WorkspaceActionController::class, 'unassignAsset'])->name('assets.unassign');

    // Knowledge base
    Route::get('/knowledge', fn () => view('admin.knowledge.index'))->name('knowledge.index');
    Route::get('/knowledge/create', fn () => view('admin.knowledge.create'))->name('knowledge.create');
    Route::get('/knowledge/{article:slug}/edit', fn (\App\Models\KnowledgeArticle $article) => view('admin.knowledge.edit', compact('article')))->name('knowledge.edit');
    Route::get('/knowledge/{article:slug}', fn (\App\Models\KnowledgeArticle $article) => view('admin.knowledge.show', compact('article')))->name('knowledge.show');
    Route::post('/knowledge/{article:slug}/vote', function (\App\Models\KnowledgeArticle $article, \Illuminate\Http\Request $request) {
        app(\App\Services\Knowledge\ArticleService::class)->vote($article, (bool) $request->input('helpful'));
        return back();
    })->name('knowledge.vote');

    // Automation & Reports
    Route::get('/automation', fn () => view('admin.automation.index'))->name('automation.index');
    Route::post('/automation/save', [App\Http\Controllers\WorkspaceActionController::class, 'saveAutomation'])->name('automation.save');
    Route::patch('/automation/{automation}/toggle', [App\Http\Controllers\WorkspaceActionController::class, 'toggleAutomation'])->name('automation.toggle');
    Route::delete('/automation/{automation}', [App\Http\Controllers\WorkspaceActionController::class, 'deleteAutomation'])->name('automation.delete');
    Route::get('/reports', fn () => view('admin.reports.index'))->name('reports.index');

    // Configuration
    Route::get('/branding', fn () => redirect()->route('admin.settings.index'))->name('branding');
    Route::post('/branding', [App\Http\Controllers\AdminController::class, 'saveBranding'])->name('branding.save');
    Route::get('/settings', fn () => view('admin.settings.index'))->name('settings.index');
    Route::post('/settings/branding', [App\Http\Controllers\AdminController::class, 'saveBranding'])->name('settings.branding.save');
    Route::post('/settings/sso', [App\Http\Controllers\AdminController::class, 'saveSsoSettings'])->name('settings.sso.save');
    Route::post('/settings/mail', [App\Http\Controllers\AdminController::class, 'saveMailSettings'])->name('settings.mail.save');
    Route::post('/settings/mail/test', [App\Http\Controllers\AdminController::class, 'testMailSettings'])->name('settings.mail.test');

    // Profile
    Route::get('/profile', fn () => view('admin.profile'))->name('profile');
    Route::patch('/profile', [App\Http\Controllers\AuthController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [App\Http\Controllers\AuthController::class, 'updatePassword'])->name('profile.password');
});


Route::middleware(['auth', 'role:admin|agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', fn () => view('agent.dashboard'))->name('dashboard');
    Route::get('/tickets', fn () => view('agent.tickets.index'))->name('tickets.index');
    Route::post('/tickets', [App\Http\Controllers\TicketController::class, 'store'])->name('tickets.store');
    Route::patch('/tickets/{ticket}/status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::post('/tickets/{ticket}/comments', [App\Http\Controllers\WorkspaceActionController::class, 'addTicketComment'])->name('tickets.comments.add');
    Route::patch('/tickets/{ticket}/status/save', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketStatus'])->name('tickets.status.save');
    Route::patch('/tickets/{ticket}/priority', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketPriority'])->name('tickets.priority.save');
    Route::patch('/tickets/{ticket}/assignee', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketAssignee'])->name('tickets.assignee.save');
    Route::post('/tickets/{ticket}/merge', [App\Http\Controllers\WorkspaceActionController::class, 'mergeTicket'])->name('tickets.merge');
    Route::post('/tickets/{ticket}/watch', [App\Http\Controllers\WorkspaceActionController::class, 'toggleTicketWatch'])->name('tickets.watch.toggle');
    Route::get('/tickets/create', \App\Livewire\Tickets\CreateTicket::class)->name('tickets.create');
    Route::get('/tickets/kanban', \App\Livewire\Tickets\TicketKanban::class)->name('tickets.kanban');
    Route::get('/tickets/{ticket:ulid}', fn (\App\Models\Ticket $ticket) => view('agent.tickets.show', compact('ticket')))->name('tickets.show');
    Route::get('/triage', fn () => view('agent.tickets.triage'))->name('tickets.triage');

    Route::get('/knowledge', fn () => view('agent.knowledge.index'))->name('knowledge.index');
    Route::get('/knowledge/create', fn () => view('agent.knowledge.create'))->name('knowledge.create');
    Route::get('/knowledge/{article:slug}/edit', function (\App\Models\KnowledgeArticle $article) {
        $user = auth()->user();
        if (! ($user->hasRole('admin') || $user->role === 'admin')) {
            $allowedTeamIds = \App\Models\Team::query()
                ->where('team_lead_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            abort_unless($article->team_id && in_array((int) $article->team_id, $allowedTeamIds, true), 403);
        }
        return view('agent.knowledge.edit', compact('article'));
    })->name('knowledge.edit');
    Route::get('/knowledge/{article:slug}', function (\App\Models\KnowledgeArticle $article) {
        $user = auth()->user();
        if (! ($user->hasRole('admin') || $user->role === 'admin')) {
            $allowedTeamIds = \App\Models\Team::query()
                ->where('team_lead_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            abort_unless($article->team_id && in_array((int) $article->team_id, $allowedTeamIds, true), 403);
        }
        return view('agent.knowledge.show', compact('article'));
    })->name('knowledge.show');
    Route::post('/knowledge/{article:slug}/vote', function (\App\Models\KnowledgeArticle $article, \Illuminate\Http\Request $request) {
        app(\App\Services\Knowledge\ArticleService::class)->vote($article, (bool) $request->input('helpful'));
        return back();
    })->name('knowledge.vote');

    // ITSM
    Route::get('/changes', fn () => view('agent.itsm.changes'))->name('changes.index');
    Route::get('/problems', fn () => view('agent.itsm.problems'))->name('problems.index');
    Route::post('/problems/{problem}/root-cause', [App\Http\Controllers\WorkspaceActionController::class, 'saveRootCause'])->name('problems.root-cause.save');
    Route::post('/problems/{problem}/incidents/{incident}/link', [App\Http\Controllers\WorkspaceActionController::class, 'linkIncident'])->name('problems.incidents.link');
    Route::delete('/problems/incidents/{incident}', [App\Http\Controllers\WorkspaceActionController::class, 'unlinkIncident'])->name('problems.incidents.unlink');
    Route::get('/assets', fn () => view('agent.itsm.assets'))->name('assets.index');
    Route::post('/assets/save', [App\Http\Controllers\WorkspaceActionController::class, 'saveAsset'])->name('assets.save');
    Route::post('/assets/import', [App\Http\Controllers\WorkspaceActionController::class, 'importAssets'])->name('assets.import');
    Route::patch('/assets/{asset}/assign', [App\Http\Controllers\WorkspaceActionController::class, 'assignAsset'])->name('assets.assign');
    Route::delete('/assets/{asset}', [App\Http\Controllers\WorkspaceActionController::class, 'deleteAsset'])->name('assets.delete');
    Route::patch('/assets/{asset}/unassign', [App\Http\Controllers\WorkspaceActionController::class, 'unassignAsset'])->name('assets.unassign');

    // Agent analytics
    Route::get('/automation', fn () => view('agent.automation.index'))->name('automation.index');
    Route::post('/automation/save', [App\Http\Controllers\WorkspaceActionController::class, 'saveAutomation'])->name('automation.save');
    Route::patch('/automation/{automation}/toggle', [App\Http\Controllers\WorkspaceActionController::class, 'toggleAutomation'])->name('automation.toggle');
    Route::delete('/automation/{automation}', [App\Http\Controllers\WorkspaceActionController::class, 'deleteAutomation'])->name('automation.delete');
    Route::get('/reports', fn () => view('agent.reports.index'))->name('reports.index');

    // Profile only — no system settings for agents
    Route::get('/profile', fn () => view('agent.settings.profile'))->name('profile');
    Route::patch('/profile', [App\Http\Controllers\AuthController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [App\Http\Controllers\AuthController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth', 'role:team_lead|admin'])->prefix('team-lead')->name('team-lead.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\TeamLeadController::class, 'dashboard'])->name('dashboard');
    Route::get('/teams', [App\Http\Controllers\TeamLeadController::class, 'teams'])->name('teams');
    Route::get('/tickets', [App\Http\Controllers\TeamLeadController::class, 'tickets'])->name('tickets');
    Route::post('/tickets/{ticket}/comments', [App\Http\Controllers\WorkspaceActionController::class, 'addTicketComment'])->name('tickets.comments.add');
    Route::patch('/tickets/{ticket}/status/save', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketStatus'])->name('tickets.status.save');
    Route::patch('/tickets/{ticket}/priority', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketPriority'])->name('tickets.priority.save');
    Route::patch('/tickets/{ticket}/assignee', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketAssignee'])->name('tickets.assignee.save');
    Route::post('/tickets/{ticket}/merge', [App\Http\Controllers\WorkspaceActionController::class, 'mergeTicket'])->name('tickets.merge');
    Route::post('/tickets/{ticket}/watch', [App\Http\Controllers\WorkspaceActionController::class, 'toggleTicketWatch'])->name('tickets.watch.toggle');
    Route::get('/tickets/{ticket:ulid}', [App\Http\Controllers\TeamLeadController::class, 'showTicket'])->name('tickets.show');
    Route::get('/knowledge', fn () => view('team-lead.knowledge.index'))->name('knowledge.index');
    Route::get('/knowledge/create', fn () => view('team-lead.knowledge.create'))->name('knowledge.create');
    Route::get('/knowledge/{article:slug}/edit', function (\App\Models\KnowledgeArticle $article) {
        $user = auth()->user();
        if (! ($user->hasRole('admin') || $user->role === 'admin')) {
            $allowedTeamIds = \App\Models\Team::query()
                ->where('team_lead_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            abort_unless($article->team_id && in_array((int) $article->team_id, $allowedTeamIds, true), 403);
        }
        return view('team-lead.knowledge.edit', compact('article'));
    })->name('knowledge.edit');
    Route::get('/knowledge/{article:slug}', function (\App\Models\KnowledgeArticle $article) {
        $user = auth()->user();
        if (! ($user->hasRole('admin') || $user->role === 'admin')) {
            $allowedTeamIds = \App\Models\Team::query()
                ->where('team_lead_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            abort_unless($article->team_id && in_array((int) $article->team_id, $allowedTeamIds, true), 403);
        }
        return view('team-lead.knowledge.show', compact('article'));
    })->name('knowledge.show');
    Route::post('/knowledge/{article:slug}/vote', function (\App\Models\KnowledgeArticle $article, \Illuminate\Http\Request $request) {
        app(\App\Services\Knowledge\ArticleService::class)->vote($article, (bool) $request->input('helpful'));
        return back();
    })->name('knowledge.vote');

    Route::get('/service-catalogue', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'index'])
        ->defaults('portal', 'team-lead')
        ->name('service-catalogue.index');
    Route::get('/service-catalogue/create', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'create'])
        ->defaults('portal', 'team-lead')
        ->name('service-catalogue.create');
    Route::post('/service-catalogue', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'store'])
        ->defaults('portal', 'team-lead')
        ->name('service-catalogue.store');
    Route::get('/service-catalogue/{item}/edit', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'edit'])
        ->defaults('portal', 'team-lead')
        ->name('service-catalogue.edit');
    Route::patch('/service-catalogue/{item}', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'update'])
        ->defaults('portal', 'team-lead')
        ->name('service-catalogue.update');
    Route::patch('/service-catalogue/{item}/toggle', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'toggle'])
        ->defaults('portal', 'team-lead')
        ->name('service-catalogue.toggle');
    Route::delete('/service-catalogue/{item}', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'destroy'])
        ->defaults('portal', 'team-lead')
        ->name('service-catalogue.destroy');
});

Route::middleware(['auth', 'role:manager|admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/teams', [App\Http\Controllers\ManagerController::class, 'teams'])->name('teams');
    Route::get('/users', [App\Http\Controllers\ManagerController::class, 'users'])->name('users');
    Route::get('/tickets', [App\Http\Controllers\ManagerController::class, 'tickets'])->name('tickets');
    Route::post('/tickets/{ticket}/comments', [App\Http\Controllers\WorkspaceActionController::class, 'addTicketComment'])->name('tickets.comments.add');
    Route::patch('/tickets/{ticket}/status/save', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketStatus'])->name('tickets.status.save');
    Route::patch('/tickets/{ticket}/priority', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketPriority'])->name('tickets.priority.save');
    Route::patch('/tickets/{ticket}/assignee', [App\Http\Controllers\WorkspaceActionController::class, 'updateTicketAssignee'])->name('tickets.assignee.save');
    Route::post('/tickets/{ticket}/merge', [App\Http\Controllers\WorkspaceActionController::class, 'mergeTicket'])->name('tickets.merge');
    Route::post('/tickets/{ticket}/watch', [App\Http\Controllers\WorkspaceActionController::class, 'toggleTicketWatch'])->name('tickets.watch.toggle');
    Route::get('/tickets/{ticket:ulid}', [App\Http\Controllers\ManagerController::class, 'showTicket'])->name('tickets.show');
    Route::get('/knowledge', fn () => view('manager.knowledge.index'))->name('knowledge.index');
    Route::get('/knowledge/create', fn () => view('manager.knowledge.create'))->name('knowledge.create');
    Route::get('/knowledge/{article:slug}/edit', function (\App\Models\KnowledgeArticle $article) {
        $user = auth()->user();
        if (! ($user->hasRole('admin') || $user->role === 'admin')) {
            $allowedTeamIds = \App\Models\Team::query()
                ->where('team_lead_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            abort_unless($article->team_id && in_array((int) $article->team_id, $allowedTeamIds, true), 403);
        }
        return view('manager.knowledge.edit', compact('article'));
    })->name('knowledge.edit');
    Route::get('/knowledge/{article:slug}', function (\App\Models\KnowledgeArticle $article) {
        $user = auth()->user();
        if (! ($user->hasRole('admin') || $user->role === 'admin')) {
            $allowedTeamIds = \App\Models\Team::query()
                ->where('team_lead_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            abort_unless($article->team_id && in_array((int) $article->team_id, $allowedTeamIds, true), 403);
        }
        return view('manager.knowledge.show', compact('article'));
    })->name('knowledge.show');
    Route::post('/knowledge/{article:slug}/vote', function (\App\Models\KnowledgeArticle $article, \Illuminate\Http\Request $request) {
        app(\App\Services\Knowledge\ArticleService::class)->vote($article, (bool) $request->input('helpful'));
        return back();
    })->name('knowledge.vote');

    Route::get('/service-catalogue', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'index'])
        ->defaults('portal', 'manager')
        ->name('service-catalogue.index');
    Route::get('/service-catalogue/create', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'create'])
        ->defaults('portal', 'manager')
        ->name('service-catalogue.create');
    Route::post('/service-catalogue', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'store'])
        ->defaults('portal', 'manager')
        ->name('service-catalogue.store');
    Route::get('/service-catalogue/{item}/edit', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'edit'])
        ->defaults('portal', 'manager')
        ->name('service-catalogue.edit');
    Route::patch('/service-catalogue/{item}', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'update'])
        ->defaults('portal', 'manager')
        ->name('service-catalogue.update');
    Route::patch('/service-catalogue/{item}/toggle', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'toggle'])
        ->defaults('portal', 'manager')
        ->name('service-catalogue.toggle');
    Route::delete('/service-catalogue/{item}', [\App\Http\Controllers\ServiceCatalogueItemController::class, 'destroy'])
        ->defaults('portal', 'manager')
        ->name('service-catalogue.destroy');
});

// Invitation acceptance (public)
Route::get('/invite/{token}', [App\Http\Controllers\InvitationController::class, 'show'])->name('invite.show');
Route::post('/invite/{token}', [App\Http\Controllers\InvitationController::class, 'accept'])->name('invite.accept');

// Change approval (public — token-authenticated via email)
Route::prefix('change-approval')->name('change.approval.')->group(function () {
    Route::get('/{token}', [App\Http\Controllers\ChangeApprovalController::class, 'show'])->name('show');
    Route::get('/{token}/approve', [App\Http\Controllers\ChangeApprovalController::class, 'quickApprove'])->name('quick-approve');
    Route::get('/{token}/reject', [App\Http\Controllers\ChangeApprovalController::class, 'quickReject'])->name('quick-reject');
    Route::post('/{token}', [App\Http\Controllers\ChangeApprovalController::class, 'submit'])->name('submit');
});

// In-app approval decision (auth required)
Route::post('/change-approval/in-app/{approver}', [App\Http\Controllers\ChangeApprovalController::class, 'inAppDecide'])
    ->middleware('auth')
    ->name('change.approval.in-app');

// Self-service portal
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/kb/search', [PortalController::class, 'searchKb'])->name('kb.search');

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', [PortalController::class, 'index'])->name('index');
        Route::get('/tickets', [PortalController::class, 'tickets'])->name('tickets.index');
        Route::get('/tickets/create', [PortalController::class, 'createTicket'])->name('tickets.create');
        Route::post('/tickets', [PortalController::class, 'storeTicket'])->name('tickets.store');
        Route::get('/catalogue', [PortalController::class, 'catalogue'])->name('catalogue.index');
        Route::get('/catalogue/{id}', [PortalController::class, 'catalogueShow'])->name('catalogue.show');
        Route::post('/catalogue/{id}', [PortalController::class, 'catalogueSubmit'])->name('catalogue.submit');
    });

    // Ticket show: auth OR guest token
    Route::get('/tickets/{ticket:ulid}', [PortalController::class, 'showTicket'])->name('tickets.show');
});

// CSAT survey response (public — token-authenticated)
Route::prefix('portal/csat')->name('portal.csat.')->group(function () {
    Route::get('/{token}/rate/{rating}', [PortalController::class, 'csatRate'])->name('rate');
    Route::get('/{token}/feedback', [PortalController::class, 'csatFeedback'])->name('feedback');
    Route::post('/{token}/feedback', [PortalController::class, 'csatStoreFeedback'])->name('feedback.store');
});

Route::prefix('install')->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('installer.index');
    Route::get('/database', [InstallerController::class, 'database'])->name('installer.database');
    Route::post('/database/test', [InstallerController::class, 'testDatabase'])->name('installer.database.test');
    Route::post('/database', [InstallerController::class, 'storeDatabase'])->name('installer.database.store');
    Route::get('/account', [InstallerController::class, 'account'])->name('installer.account');
    Route::post('/account', [InstallerController::class, 'storeAccount'])->name('installer.account.store');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('installer.finish');
});
