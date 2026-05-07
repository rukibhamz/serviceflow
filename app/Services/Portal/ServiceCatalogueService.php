<?php

namespace App\Services\Portal;

use App\Models\ServiceCatalogueItem;
use App\Models\User;

class ServiceCatalogueService
{
    /** @return array<int, array<string, mixed>> */
    public function all(?User $user = null): array
    {
        $configItems = config('catalogue.items', []);
        $allowedTeamIds = $this->resolveAllowedTeamIds($user);
        $dbItems = ServiceCatalogueItem::query()
            ->where('is_active', true)
            ->when($user?->tenant_id, fn ($q) => $q->where(function ($nested) use ($user) {
                $nested->whereNull('tenant_id')->orWhere('tenant_id', $user->tenant_id);
            }))
            ->when($user, fn ($q) => $q->where(function ($nested) use ($allowedTeamIds) {
                $nested->whereNull('team_id');
                if (! empty($allowedTeamIds)) {
                    $nested->orWhereIn('team_id', $allowedTeamIds);
                }
            }))
            ->orderBy('name')
            ->get()
            ->map(function (ServiceCatalogueItem $item): array {
                return [
                    'id' => $item->slug,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'priority' => $item->priority,
                    'fields' => is_array($item->fields) ? $item->fields : [],
                ];
            })
            ->all();

        return array_values(array_merge($dbItems, $configItems));
    }

    /** @return array<string, mixed>|null */
    public function find(string $id, ?User $user = null): ?array
    {
        foreach ($this->all($user) as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Merge catalogue item type/priority with submitted form data.
     * Custom field values are stored under the `custom_fields` key.
     *
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function mapToTicketData(string $catalogueId, array $formData, ?User $user = null): array
    {
        $item = $this->find($catalogueId, $user);

        if ($item === null) {
            throw new \InvalidArgumentException("Catalogue item '{$catalogueId}' not found.");
        }

        $customFields = [];
        foreach ($item['fields'] as $field) {
            $name = $field['name'];
            if (isset($formData[$name])) {
                $customFields[$name] = $formData[$name];
            }
        }

        return [
            'subject'       => $formData['subject'] ?? $item['name'],
            'description'   => $formData['description'] ?? null,
            'type'          => $item['type'],
            'priority'      => $item['priority'],
            'source'        => 'web',
            'custom_fields' => $customFields,
        ];
    }

    /** @return array<int, int> */
    private function resolveAllowedTeamIds(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasRole('admin') || $user->role === 'admin' || $user->hasRole('manager') || $user->role === 'manager') {
            return \App\Models\Team::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return \App\Models\Team::query()
            ->where('team_lead_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
