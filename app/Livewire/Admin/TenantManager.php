<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioner;
use Livewire\Component;
use Livewire\WithPagination;

class TenantManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    // Provision form
    public string $name          = '';
    public string $subdomain     = '';
    public string $adminName     = '';
    public string $adminEmail    = '';
    public string $adminPassword = '';

    protected $rules = [
        'name'          => 'required|string|max:255',
        'subdomain'     => 'required|string|max:63|regex:/^[a-z0-9-]+$/',
        'adminName'     => 'required|string|max:255',
        'adminEmail'    => 'required|email',
        'adminPassword' => 'required|string|min:8',
    ];

    public function provision(): void
    {
        $this->validate();

        try {
            app(TenantProvisioner::class)->provision([
                'name'           => $this->name,
                'subdomain'      => $this->subdomain,
                'admin_name'     => $this->adminName,
                'admin_email'    => $this->adminEmail,
                'admin_password' => $this->adminPassword,
            ]);

            session()->flash('success', "Tenant '{$this->name}' provisioned successfully.");
            $this->resetForm();
        } catch (\InvalidArgumentException $e) {
            $this->addError('subdomain', $e->getMessage());
        }
    }

    public function suspend(int $id): void
    {
        app(TenantProvisioner::class)->suspend(Tenant::findOrFail($id));
        session()->flash('success', 'Tenant suspended.');
    }

    public function activate(int $id): void
    {
        app(TenantProvisioner::class)->activate(Tenant::findOrFail($id));
        session()->flash('success', 'Tenant activated.');
    }

    private function resetForm(): void
    {
        $this->name          = '';
        $this->subdomain     = '';
        $this->adminName     = '';
        $this->adminEmail    = '';
        $this->adminPassword = '';
        $this->showForm      = false;
    }

    public function render()
    {
        $tenants = Tenant::withTrashed()->latest()->paginate(20);

        return view('livewire.admin.tenant-manager', compact('tenants'));
    }
}
