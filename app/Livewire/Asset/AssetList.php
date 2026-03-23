<?php

namespace App\Livewire\Asset;

use App\Models\Asset;
use App\Models\User;
use App\Services\Asset\AssetImporter;
use App\Services\Asset\AssetService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AssetList extends Component
{
    use WithPagination, WithFileUploads;

    // ── Filters ───────────────────────────────────────────────────────────────
    public string $search   = '';
    public string $typeFilter   = '';
    public string $statusFilter = '';

    // ── Detail / edit panel ───────────────────────────────────────────────────
    public ?int $viewingId  = null;
    public ?int $editingId  = null;
    public string $name         = '';
    public string $type         = '';
    public string $serialNumber = '';
    public string $assetTag     = '';
    public string $status       = 'available';
    public ?string $purchasedAt = null;
    public bool $showForm       = false;

    // ── Assignment panel ──────────────────────────────────────────────────────
    public ?int $assigningId  = null;
    public string $assigneeSearch = '';

    // ── Import wizard ─────────────────────────────────────────────────────────
    public bool $showImport    = false;
    public $importFile         = null;
    public ?array $importErrors = null;
    public ?int $importCreated  = null;

    protected $rules = [
        'name'   => 'required|string|max:255',
        'type'   => 'required|string|max:100',
        'status' => 'required|string',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function newAsset(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $asset = Asset::findOrFail($id);
        $this->editingId    = $id;
        $this->name         = $asset->name;
        $this->type         = $asset->type;
        $this->serialNumber = $asset->serial_number ?? '';
        $this->assetTag     = $asset->asset_tag ?? '';
        $this->status       = $asset->status;
        $this->purchasedAt  = $asset->purchased_at?->format('Y-m-d');
        $this->showForm     = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'          => $this->name,
            'type'          => $this->type,
            'serial_number' => $this->serialNumber ?: null,
            'asset_tag'     => $this->assetTag ?: null,
            'status'        => $this->status,
            'purchased_at'  => $this->purchasedAt ?: null,
        ];

        $service = app(AssetService::class);

        if ($this->editingId) {
            $service->update(Asset::findOrFail($this->editingId), $data);
            session()->flash('success', 'Asset updated.');
        } else {
            $service->create($data);
            session()->flash('success', 'Asset created.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        app(AssetService::class)->delete(Asset::findOrFail($id));
        session()->flash('success', 'Asset deleted.');
    }

    // ── Assignment ────────────────────────────────────────────────────────────

    public function openAssign(int $id): void
    {
        $this->assigningId    = $id;
        $this->assigneeSearch = '';
    }

    public function assignTo(int $userId): void
    {
        $asset = Asset::findOrFail($this->assigningId);
        $user  = User::findOrFail($userId);
        app(AssetService::class)->assign($asset, $user);
        $this->assigningId = null;
        session()->flash('success', 'Asset assigned.');
    }

    public function unassign(int $id): void
    {
        app(AssetService::class)->unassign(Asset::findOrFail($id));
        session()->flash('success', 'Asset unassigned.');
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public function runImport(): void
    {
        $this->validate(['importFile' => 'required|file|mimes:csv,xlsx,xls|max:5120']);

        $path   = $this->importFile->getRealPath();
        $result = app(AssetImporter::class)->import($path);

        $this->importCreated = $result->created;
        $this->importErrors  = $result->errors;
        $this->importFile    = null;
    }

    private function resetForm(): void
    {
        $this->editingId    = null;
        $this->name         = '';
        $this->type         = '';
        $this->serialNumber = '';
        $this->assetTag     = '';
        $this->status       = 'available';
        $this->purchasedAt  = null;
        $this->showForm     = false;
    }

    public function render()
    {
        $assets = Asset::with('assignee')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('serial_number', 'like', "%{$this->search}%")
                  ->orWhere('asset_tag', 'like', "%{$this->search}%");
            }))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);

        $assigneeResults = $this->assigningId && $this->assigneeSearch
            ? User::where('name', 'like', "%{$this->assigneeSearch}%")->limit(10)->get()
            : collect();

        $statuses = app(AssetService::class)->validStatuses();

        return view('livewire.asset.asset-list', compact('assets', 'assigneeResults', 'statuses'));
    }
}
