<?php

namespace App\Livewire\Automation;

use App\Models\Automation;
use App\Services\Automation\TriggerRegistry;
use Livewire\Component;
use Livewire\WithPagination;

class AutomationBuilder extends Component
{
    use WithPagination;

    // ── Form state ────────────────────────────────────────────────────────────
    public ?int $editingId = null;
    public string $name         = '';
    public string $triggerEvent = '';
    public string $condOperator = 'AND';
    public array $conditions    = [];
    public array $actions       = [];
    public bool $isActive       = true;
    public bool $showForm       = false;

    protected $rules = [
        'name'         => 'required|string|max:255',
        'triggerEvent' => 'required|string',
        'condOperator' => 'required|in:AND,OR',
        'conditions'   => 'array',
        'actions'      => 'array',
    ];

    public function mount(): void
    {
        $this->resetForm();

        if (request()->boolean('new')) {
            $this->showForm = true;
        }
    }

    // ── Condition management ──────────────────────────────────────────────────

    public function addCondition(): void
    {
        $this->conditions[] = ['field' => 'priority', 'op' => 'equals', 'value' => ''];
    }

    public function removeCondition(int $index): void
    {
        array_splice($this->conditions, $index, 1);
        $this->conditions = array_values($this->conditions);
    }

    // ── Action management ─────────────────────────────────────────────────────

    public function addAction(): void
    {
        $this->actions[] = ['type' => 'add_comment', 'body' => '', 'internal' => true];
    }

    public function removeAction(int $index): void
    {
        array_splice($this->actions, $index, 1);
        $this->actions = array_values($this->actions);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'          => $this->name,
            'trigger_event' => $this->triggerEvent,
            'conditions'    => ['operator' => $this->condOperator, 'conditions' => $this->conditions],
            'actions'       => $this->actions,
            'is_active'     => $this->isActive,
        ];

        if ($this->editingId) {
            Automation::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Automation updated.');
        } else {
            Automation::create($data);
            session()->flash('success', 'Automation created.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $automation = Automation::findOrFail($id);

        $this->editingId    = $id;
        $this->name         = $automation->name;
        $this->triggerEvent = $automation->trigger_event;
        $this->isActive     = $automation->is_active;

        $tree = $automation->conditions ?? [];
        $this->condOperator = $tree['operator'] ?? 'AND';
        $this->conditions   = $tree['conditions'] ?? [];
        $this->actions      = $automation->actions ?? [];
        $this->showForm     = true;
    }

    public function delete(int $id): void
    {
        Automation::findOrFail($id)->delete();
        session()->flash('success', 'Automation deleted.');
    }

    public function toggleActive(int $id): void
    {
        $automation = Automation::findOrFail($id);
        $automation->is_active = ! $automation->is_active;
        $automation->save();
    }

    public function newAutomation(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId    = null;
        $this->name         = '';
        $this->triggerEvent = '';
        $this->condOperator = 'AND';
        $this->conditions   = [];
        $this->actions      = [];
        $this->isActive     = true;
        $this->showForm     = false;
    }

    public function render()
    {
        $triggers    = app(TriggerRegistry::class)->all();
        $automations = Automation::latest()->paginate(15);

        return view('livewire.automation.automation-builder', compact('triggers', 'automations'));
    }
}
