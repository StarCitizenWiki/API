<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests for get admin/game-versions to login', function (): void {
    $response = $this->get(route('admin.game-versions.index'));

    $response->assertRedirect(route('login'));
});

it('forbids authenticated non-admin users for get admin/game-versions', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)
        ->get(route('admin.game-versions.index'));

    $response->assertForbidden();
});

it('allows authenticated admins and returns game versions in the index view', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $latestVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.1',
        'released_at' => now(),
        'is_default' => true,
    ]);
    $olderVersion = GameVersion::factory()->create([
        'code' => '3.24.2-PTU.5',
        'released_at' => now()->subDay(),
        'is_default' => false,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.game-versions.index'));

    $response->assertSuccessful();
    $response->assertViewIs('admin.game-versions.index');
    $response->assertViewHas('versions', function ($versions) use ($latestVersion, $olderVersion): bool {
        return $versions->contains($latestVersion)
            && $versions->contains($olderVersion);
    });
    $response->assertSeeText([
        $latestVersion->code,
        $olderVersion->code,
    ]);
});

it('redirects guests for post admin/game-versions/{gameversion}/set-default to login', function (): void {
    $gameVersion = GameVersion::factory()->create();

    $response = $this->post(route('admin.game-versions.set-default', $gameVersion));

    $response->assertRedirect(route('login'));
});

it('forbids authenticated non-admin users for post admin/game-versions/{gameversion}/set-default', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    $gameVersion = GameVersion::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('admin.game-versions.set-default', $gameVersion));

    $response->assertForbidden();
});

it('allows authenticated admins to set exactly one selected version as default', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $currentDefaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.1',
        'is_default' => true,
    ]);
    $selectedVersion = GameVersion::factory()->create([
        'code' => '4.1.0-PTU.2',
        'is_default' => false,
    ]);
    $otherVersion = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE.3',
        'is_default' => false,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.game-versions.set-default', $selectedVersion));

    $response->assertRedirect(route('admin.game-versions.index'));
    $response->assertSessionHas('success', "Game version {$selectedVersion->code} set as default.");

    expect(GameVersion::query()->where('is_default', true)->count())->toBe(1);

    $this->assertDatabaseHas('game_versions', [
        'id' => $selectedVersion->id,
        'is_default' => true,
    ]);
    $this->assertDatabaseHas('game_versions', [
        'id' => $currentDefaultVersion->id,
        'is_default' => false,
    ]);
    $this->assertDatabaseHas('game_versions', [
        'id' => $otherVersion->id,
        'is_default' => false,
    ]);
});
