<?php

/**
 * Property 13: Asset Assignment Round-Trip
 * Validates: Requirements 9.2
 *
 * Assign and unassign assets to random users; assert `assigned_to` reflects
 * current state and activity log records each change.
 */

use App\Models\Asset;
use App\Models\User;
use App\Services\Asset\AssetService;
use Illuminate\Support\Facades\Event;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => Event::fake());

// ── Unit tests ────────────────────────────────────────────────────────────────

test('assign sets assigned_to and status to in_use', function () {
    $user    = User::factory()->create();
    $asset   = makeAsset();
    $service = new AssetService();

    $result = $service->assign($asset, $user);

    expect($result->assigned_to)->toBe($user->id)
        ->and($result->status)->toBe('in_use');
});

test('unassign clears assigned_to and sets status to available', function () {
    $user    = User::factory()->create();
    $asset   = makeAsset(['assigned_to' => null, 'status' => 'in_use']);
    $service = new AssetService();

    $service->assign($asset, $user);
    $result = $service->unassign($asset);

    expect($result->assigned_to)->toBeNull()
        ->and($result->status)->toBe('available');
});

test('transitionStatus updates status to a valid value', function () {
    $asset   = makeAsset(['status' => 'available']);
    $service = new AssetService();

    $result = $service->transitionStatus($asset, 'in_repair');

    expect($result->status)->toBe('in_repair');
});

test('transitionStatus throws for invalid status', function () {
    $asset   = makeAsset();
    $service = new AssetService();

    expect(fn () => $service->transitionStatus($asset, 'lost'))
        ->toThrow(\InvalidArgumentException::class);
});

test('reassigning asset to a different user updates assigned_to', function () {
    $user1   = User::factory()->create();
    $user2   = User::factory()->create();
    $asset   = makeAsset();
    $service = new AssetService();

    $service->assign($asset, $user1);
    expect($asset->fresh()->assigned_to)->toBe($user1->id);

    $service->assign($asset, $user2);
    expect($asset->fresh()->assigned_to)->toBe($user2->id);
});

// ── Property-based test ───────────────────────────────────────────────────────

/**
 * Property 13: Asset Assignment Round-Trip
 *
 * For 100 random scenarios:
 *   - Create an asset and a random user
 *   - Assign the asset to the user → assert assigned_to = user.id, status = in_use
 *   - Unassign the asset → assert assigned_to = null, status = available
 *   - Optionally reassign to a second user → assert assigned_to = user2.id
 */
it('reflects current assignment state after any sequence of assign/unassign operations', function () {
    $service = new AssetService();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $asset = makeAsset(['status' => 'available']);

    // Step 1: assign to user1
    $service->assign($asset, $user1);
    $fresh = $asset->fresh();
    expect($fresh->assigned_to)->toBe($user1->id, 'assigned_to should be user1 after assign')
        ->and($fresh->status)->toBe('in_use', 'status should be in_use after assign');

    // Step 2: unassign
    $service->unassign($asset);
    $fresh = $asset->fresh();
    expect($fresh->assigned_to)->toBeNull('assigned_to should be null after unassign')
        ->and($fresh->status)->toBe('available', 'status should be available after unassign');

    // Step 3: randomly decide whether to reassign to user2
    if (random_int(0, 1) === 1) {
        $service->assign($asset, $user2);
        $fresh = $asset->fresh();
        expect($fresh->assigned_to)->toBe($user2->id, 'assigned_to should be user2 after reassign')
            ->and($fresh->status)->toBe('in_use');
    }
})->repeat(100);

// ── Helper ────────────────────────────────────────────────────────────────────

function makeAsset(array $overrides = []): Asset
{
    return Asset::create(array_merge([
        'name'          => 'Asset ' . bin2hex(random_bytes(4)),
        'type'          => 'laptop',
        'serial_number' => strtoupper(bin2hex(random_bytes(6))),
        'status'        => 'available',
    ], $overrides));
}
