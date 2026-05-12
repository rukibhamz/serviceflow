<?php

namespace App\Http\Controllers;

use App\Models\ServiceCatalogueItem;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceCatalogueItemController extends Controller
{
    public function index(string $portal)
    {
        if (! ServiceCatalogueItem::isAvailable()) {
            return view($portal.'.service-catalogue.index', ['items' => collect()]);
        }

        $user = auth()->user();
        $teamIds = $this->allowedTeamIds($user, $portal);

        $query = ServiceCatalogueItem::query()
            ->with('team')
            ->latest('id');

        if (! $this->isAdmin($user) && $portal === 'team-lead') {
            $query->whereIn('team_id', $teamIds);
        }

        $items = $query->get();

        return view($portal . '.service-catalogue.index', compact('items'));
    }

    public function create(string $portal)
    {
        if ($redirect = $this->redirectUnlessCatalogueReady($portal)) {
            return $redirect;
        }

        $user = auth()->user();
        $teamIds = $this->allowedTeamIds($user, $portal);

        $teams = Team::query()
            ->when(! $this->isAdmin($user) && $portal === 'team-lead', fn ($q) => $q->whereIn('id', $teamIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view($portal . '.service-catalogue.create', compact('teams'));
    }

    public function store(Request $request, string $portal): RedirectResponse
    {
        if ($redirect = $this->redirectUnlessCatalogueReady($portal)) {
            return $redirect;
        }

        $user = auth()->user();
        $teamIds = $this->allowedTeamIds($user, $portal);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:incident,service_request,problem,change'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'fields_json' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->assertTeamScope($portal, $data['team_id'] ?? null, $teamIds);

        $fields = $this->parseFields($data['fields_json'] ?? null);

        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (ServiceCatalogueItem::where('slug', $slug)->exists()) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        ServiceCatalogueItem::create([
            'tenant_id' => $user->tenant_id,
            'team_id' => $data['team_id'] ?? null,
            'created_by' => $user->id,
            'slug' => $slug,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'priority' => $data['priority'],
            'fields' => $fields,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()
            ->route($portal . '.service-catalogue.index')
            ->with('success', 'Service catalogue item created.');
    }

    public function edit(string $portal, ServiceCatalogueItem $item)
    {
        $user = auth()->user();
        $teamIds = $this->allowedTeamIds($user, $portal);
        $this->assertCanManageItem($portal, $item, $teamIds);

        $teams = Team::query()
            ->when(! $this->isAdmin($user) && $portal === 'team-lead', fn ($q) => $q->whereIn('id', $teamIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view($portal . '.service-catalogue.edit', compact('item', 'teams'));
    }

    public function update(Request $request, string $portal, ServiceCatalogueItem $item): RedirectResponse
    {
        $user = auth()->user();
        $teamIds = $this->allowedTeamIds($user, $portal);
        $this->assertCanManageItem($portal, $item, $teamIds);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:incident,service_request,problem,change'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'fields_json' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->assertTeamScope($portal, $data['team_id'] ?? null, $teamIds);
        $fields = $this->parseFields($data['fields_json'] ?? null);

        $item->update([
            'team_id' => $data['team_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'priority' => $data['priority'],
            'fields' => $fields,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()
            ->route($portal . '.service-catalogue.index')
            ->with('success', 'Service catalogue item updated.');
    }

    public function toggle(string $portal, ServiceCatalogueItem $item): RedirectResponse
    {
        $teamIds = $this->allowedTeamIds(auth()->user(), $portal);
        $this->assertCanManageItem($portal, $item, $teamIds);

        $item->update(['is_active' => ! $item->is_active]);

        return back()->with('success', 'Service catalogue item status updated.');
    }

    public function destroy(string $portal, ServiceCatalogueItem $item): RedirectResponse
    {
        $teamIds = $this->allowedTeamIds(auth()->user(), $portal);
        $this->assertCanManageItem($portal, $item, $teamIds);

        $item->delete();

        return back()->with('success', 'Service catalogue item deleted.');
    }

    private function redirectUnlessCatalogueReady(string $portal): ?RedirectResponse
    {
        if (ServiceCatalogueItem::isAvailable()) {
            return null;
        }

        return redirect()
            ->route($portal.'.dashboard')
            ->with('error', 'Database upgrade required: run php artisan migrate --force on the server.');
    }

    private function parseFields(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw ValidationException::withMessages(['fields_json' => 'Fields JSON must be a valid JSON array.']);
        }

        foreach ($decoded as $field) {
            if (! is_array($field) || empty($field['name']) || empty($field['label'])) {
                throw ValidationException::withMessages([
                    'fields_json' => 'Each field must include at least "name" and "label".',
                ]);
            }
        }

        return $decoded;
    }

    private function allowedTeamIds($user, string $portal): array
    {
        if ($this->isAdmin($user) || $portal === 'manager') {
            return Team::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return Team::query()
            ->where('team_lead_id', $user->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function assertCanManageItem(string $portal, ServiceCatalogueItem $item, array $teamIds): void
    {
        if ($portal === 'team-lead' && ! in_array((int) $item->team_id, $teamIds, true)) {
            abort(403, 'You are not allowed to manage this service catalogue item.');
        }
    }

    private function assertTeamScope(string $portal, ?string $teamId, array $teamIds): void
    {
        if ($portal === 'team-lead' && empty($teamId)) {
            throw ValidationException::withMessages(['team_id' => 'Team is required for Team Lead catalogue items.']);
        }

        if ($portal === 'team-lead' && ! empty($teamId) && ! in_array((int) $teamId, $teamIds, true)) {
            throw ValidationException::withMessages(['team_id' => 'You can only create catalogue items for your own teams.']);
        }
    }

    private function isAdmin($user): bool
    {
        return $user && ($user->hasRole('admin') || $user->role === 'admin');
    }
}

