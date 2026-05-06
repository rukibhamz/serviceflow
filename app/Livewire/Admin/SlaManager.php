<?php

namespace App\Livewire\Admin;

use App\Models\SlaPolicy;
use Livewire\Component;
use Livewire\WithPagination;

class SlaManager extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;

    // Form fields
    public string $name               = '';
    public string $priority           = 'medium';
    public string $ticketType         = '';
    public int    $responseMinutes    = 60;
    public int    $resolutionMinutes  = 480;
    public bool   $businessHoursOnly  = false;
    public bool   $isDefault          = false;
    public bool   $isActive           = true;

    protected $rules = [
        'name'              => 'required|string|max:255',
        'priority'          => 'required|in:low,medium,high,critical,urgent',
        'ticketType'        => 'nullable|string',
        'responseMinutes'   => 'required|integer|min:1',
        'resolutionMinutes' => 'required|integer|min:1',
    ];

    public function mount(): void
    {
        if (request()->boolean('new')) {
            $this->showForm = true;
        }
    }

    public function newPolicy(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $policy = SlaPolicy::findOrFail($id);
        $this->editingId          = $id;
        $this->name               = $policy->name;
        $this->priority           = $policy->priority;
        $this->ticketType         = $policy->ticket_type ?? '';
        $this->responseMinutes    = $policy->response_minutes;
        $this->resolutionMinutes  = $policy->resolution_minutes;
        $this->businessHoursOnly  = ! empty($policy->business_hours);
        $this->isDefault          = $policy->is_default;
        $this->isActive           = $policy->is_active;
        $this->showForm           = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'               => $this->name,
            'priority'           => $this->priority,
            'ticket_type'        => $this->ticketType ?: null,
            'response_minutes'   => $this->responseMinutes,
            'resolution_minutes' => $this->resolutionMinutes,
            'business_hours'     => $this->businessHoursOnly
                ? ['start' => '09:00', 'end' => '17:00', 'days' => [1,2,3,4,5]]
                : null,
            'is_default'         => $this->isDefault,
            'is_active'          => $this->isActive,
        ];

        if ($this->isDefault) {
            // Only one default per priority+type combination
            SlaPolicy::where('priority', $this->priority)
                ->where('ticket_type', $data['ticket_type'])
                ->where('id', '!=', $this->editingId ?? 0)
                ->update(['is_default' => false]);
        }

        if ($this->editingId) {
            SlaPolicy::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'SLA policy updated.');
        } else {
            SlaPolicy::create($data);
            session()->flash('success', 'SLA policy created.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        SlaPolicy::findOrFail($id)->delete();
        session()->flash('success', 'SLA policy deleted.');
    }

    public function toggleActive(int $id): void
    {
        $policy = SlaPolicy::findOrFail($id);
        $policy->is_active = ! $policy->is_active;
        $policy->save();
    }

    private function resetForm(): void
    {
        $this->editingId         = null;
        $this->name              = '';
        $this->priority          = 'medium';
        $this->ticketType        = '';
        $this->responseMinutes   = 60;
        $this->resolutionMinutes = 480;
        $this->businessHoursOnly = false;
        $this->isDefault         = false;
        $this->isActive          = true;
        $this->showForm          = false;
    }

    private function formatMinutes(int $minutes): string
    {
        if ($minutes < 60) return "{$minutes}m";
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
    }

    public function render()
    {
        return view('livewire.admin.sla-manager', [
            'policies' => SlaPolicy::orderBy('priority')->orderBy('name')->paginate(20),
        ])->layout('layouts.admin');
    }
}
