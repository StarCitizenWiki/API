<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\User;

it('allows authenticated admins to see game versions in the index', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $newestVersion = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE.1',
        'released_at' => now(),
        'is_default' => false,
        'is_hidden' => false,
    ]);

    $defaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.1',
        'released_at' => now()->subDay(),
        'is_default' => true,
        'is_hidden' => false,
    ]);

    $hiddenVersion = GameVersion::factory()->create([
        'code' => '3.24.2-PTU.5',
        'released_at' => now()->subDays(2),
        'is_default' => false,
        'is_hidden' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.game-versions.index'));

    $response->assertSuccessful()
        ->assertSeeText('Game Versions')
        ->assertSeeText('Total: 3')
        ->assertSeeTextInOrder([
            $newestVersion->code,
            $defaultVersion->code,
            $hiddenVersion->code,
        ])
        ->assertSeeText('Default')
        ->assertSeeText('Visible')
        ->assertSeeText('Hidden');

    $response->assertSee(route('admin.game-versions.set-default', $newestVersion), false)
        ->assertSee(route('admin.game-versions.hide', $newestVersion), false)
        ->assertDontSee(route('admin.game-versions.show', $newestVersion), false)
        ->assertDontSee(route('admin.game-versions.set-default', $defaultVersion), false)
        ->assertDontSee(route('admin.game-versions.hide', $defaultVersion), false)
        ->assertDontSee(route('admin.game-versions.show', $defaultVersion), false)
        ->assertSee(route('admin.game-versions.set-default', $hiddenVersion), false)
        ->assertSee(route('admin.game-versions.show', $hiddenVersion), false)
        ->assertDontSee(route('admin.game-versions.hide', $hiddenVersion), false);
});

it('allows authenticated admins to set exactly one selected version as default and makes it visible', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $currentDefaultVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE.1',
        'is_default' => true,
    ]);
    $selectedVersion = GameVersion::factory()->create([
        'code' => '4.1.0-PTU.2',
        'is_default' => false,
        'is_hidden' => true,
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
        'is_hidden' => false,
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

it('allows authenticated admins to hide non-default versions', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $gameVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => false,
        'is_hidden' => false,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.game-versions.hide', $gameVersion));

    $response->assertRedirect(route('admin.game-versions.index'));
    $response->assertSessionHas('success', "Game version {$gameVersion->code} hidden from the selector.");

    $this->assertDatabaseHas('game_versions', [
        'id' => $gameVersion->id,
        'is_hidden' => true,
    ]);
});

it('allows authenticated admins to show hidden versions', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $gameVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_hidden' => true,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.game-versions.show', $gameVersion));

    $response->assertRedirect(route('admin.game-versions.index'));
    $response->assertSessionHas('success', "Game version {$gameVersion->code} shown in the selector.");

    $this->assertDatabaseHas('game_versions', [
        'id' => $gameVersion->id,
        'is_hidden' => false,
    ]);
});

it('does not allow authenticated admins to hide the default version', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $gameVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
        'is_hidden' => false,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.game-versions.hide', $gameVersion));

    $response->assertRedirect(route('admin.game-versions.index'));
    $response->assertSessionHas('error', "Game version {$gameVersion->code} is the default version and cannot be hidden.");

    $this->assertDatabaseHas('game_versions', [
        'id' => $gameVersion->id,
        'is_hidden' => false,
    ]);
});