<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Automation;
use App\Models\Ticket;
use App\Services\Asset\AssetImporter;
use App\Services\Asset\AssetService;
use App\Services\Problem\ProblemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceActionController extends Controller
{
    public function saveAutomation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'editing_id' => 'nullable|integer|exists:automations,id',
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string',
            'cond_operator' => 'required|in:AND,OR',
            'conditions' => 'array',
            'conditions.*.field' => 'nullable|string',
            'conditions.*.op' => 'nullable|string',
            'conditions.*.value' => 'nullable|string',
            'actions' => 'array',
            'actions.*.type' => 'nullable|string',
            'actions.*.body' => 'nullable|string',
            'actions.*.status' => 'nullable|string',
            'actions.*.assignee_id' => 'nullable',
            'actions.*.url' => 'nullable|string',
            'actions.*.user_id' => 'nullable',
            'actions.*.message' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $payload = [
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'conditions' => [
                'operator' => $data['cond_operator'],
                'conditions' => array_values($data['conditions'] ?? []),
            ],
            'actions' => array_values($data['actions'] ?? []),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if (! empty($data['editing_id'])) {
            Automation::findOrFail($data['editing_id'])->update($payload);
            return back()->with('success', 'Automation updated.');
        }

        Automation::create($payload);
        return back()->with('success', 'Automation created.');
    }

    public function toggleAutomation(Automation $automation): RedirectResponse
    {
        $automation->is_active = ! $automation->is_active;
        $automation->save();

        return back()->with('success', 'Automation status updated.');
    }

    public function deleteAutomation(Automation $automation): RedirectResponse
    {
        $automation->delete();

        return back()->with('success', 'Automation deleted.');
    }

    public function saveAsset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'editing_id' => 'nullable|integer|exists:assets,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'asset_tag' => 'nullable|string|max:255',
            'status' => 'required|string',
            'purchased_at' => 'nullable|date',
        ]);

        $payload = [
            'name' => $data['name'],
            'type' => $data['type'],
            'serial_number' => $data['serial_number'] ?: null,
            'asset_tag' => $data['asset_tag'] ?: null,
            'status' => $data['status'],
            'purchased_at' => $data['purchased_at'] ?: null,
        ];

        $service = app(AssetService::class);

        if (! empty($data['editing_id'])) {
            $service->update(Asset::findOrFail($data['editing_id']), $payload);
            return back()->with('success', 'Asset updated.');
        }

        $service->create($payload);
        return back()->with('success', 'Asset created.');
    }

    public function importAssets(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $result = app(AssetImporter::class)->import($request->file('import_file')->getRealPath());
        $message = "Imported {$result->created} asset(s).";
        if (! empty($result->errors)) {
            $message .= ' Some rows failed validation.';
        }

        return back()->with('success', $message);
    }

    public function deleteAsset(Asset $asset): RedirectResponse
    {
        app(AssetService::class)->delete($asset);
        return back()->with('success', 'Asset deleted.');
    }

    public function unassignAsset(Asset $asset): RedirectResponse
    {
        app(AssetService::class)->unassign($asset);
        return back()->with('success', 'Asset unassigned.');
    }

    public function saveRootCause(Request $request, Ticket $problem): RedirectResponse
    {
        abort_unless($problem->type === 'problem', 422);

        $data = $request->validate([
            'root_cause' => 'nullable|string',
            'workaround' => 'nullable|string',
            'mark_known_error' => 'nullable|boolean',
        ]);

        $service = app(ProblemService::class);
        $service->recordRootCause($problem, (string) ($data['root_cause'] ?? ''));
        if ((bool) ($data['mark_known_error'] ?? false)) {
            $service->markKnownError($problem, (string) ($data['workaround'] ?? ''));
        }

        return back()->with('success', 'Root cause saved.');
    }

    public function linkIncident(Ticket $problem, Ticket $incident): RedirectResponse
    {
        abort_unless($problem->type === 'problem', 422);
        abort_unless($incident->type === 'incident', 422);

        app(ProblemService::class)->linkIncidents($problem, [$incident->id]);
        return back()->with('success', 'Incident linked.');
    }

    public function unlinkIncident(Ticket $incident): RedirectResponse
    {
        abort_unless($incident->type === 'incident', 422);

        app(ProblemService::class)->unlinkIncident($incident);
        return back()->with('success', 'Incident unlinked.');
    }
}

