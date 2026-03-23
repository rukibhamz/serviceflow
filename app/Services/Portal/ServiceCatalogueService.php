<?php

namespace App\Services\Portal;

class ServiceCatalogueService
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return config('catalogue.items', []);
    }

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array
    {
        foreach ($this->all() as $item) {
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
    public function mapToTicketData(string $catalogueId, array $formData): array
    {
        $item = $this->find($catalogueId);

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
}
