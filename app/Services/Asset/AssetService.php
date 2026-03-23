<?php

namespace App\Services\Asset;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * CRUD and lifecycle management for IT assets.
 *
 * Valid statuses: in_use, available, retired, in_repair, disposed
 */
class AssetService
{
    private const VALID_STATUSES = ['in_use', 'available', 'retired', 'in_repair', 'disposed'];

    // ── CRUD ──────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Asset
    {
        return Asset::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Asset $asset, array $data): Asset
    {
        $asset->update($data);

        return $asset->fresh();
    }

    public function delete(Asset $asset): void
    {
        $asset->delete();
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Asset::with('assignee')->latest()->paginate($perPage);
    }

    public function find(int $id): ?Asset
    {
        return Asset::find($id);
    }

    // ── Assignment ────────────────────────────────────────────────────────────

    /**
     * Assign an asset to a user and set status to in_use.
     */
    public function assign(Asset $asset, User $user): Asset
    {
        $asset->assigned_to = $user->id;
        $asset->status      = 'in_use';
        $asset->save();

        return $asset->fresh();
    }

    /**
     * Unassign an asset and set status to available.
     */
    public function unassign(Asset $asset): Asset
    {
        $asset->assigned_to = null;
        $asset->status      = 'available';
        $asset->save();

        return $asset->fresh();
    }

    // ── Status transitions ────────────────────────────────────────────────────

    /**
     * Transition an asset to a new status.
     *
     * @throws \InvalidArgumentException for unknown statuses
     */
    public function transitionStatus(Asset $asset, string $newStatus): Asset
    {
        if (! in_array($newStatus, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid asset status: '{$newStatus}'.");
        }

        $asset->status = $newStatus;
        $asset->save();

        return $asset->fresh();
    }

    /**
     * Returns all valid asset statuses.
     *
     * @return string[]
     */
    public function validStatuses(): array
    {
        return self::VALID_STATUSES;
    }
}
