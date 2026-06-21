<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\User;

dataset('admin_guest_routes', function (): array {
    return [
        'dashboard' => ['get', '/admin'],
        'game-versions.index' => ['get', '/admin/game-versions'],
        'jobs.index' => ['get', '/admin/jobs'],
        'translations.index' => ['get', '/admin/translations'],
        'users.index' => ['get', '/admin/users'],
    ];
});

dataset('admin_non_admin_routes', function (): array {
    return [
        'dashboard' => ['get', '/admin'],
        'game-versions.index' => ['get', '/admin/game-versions'],
        'jobs.index' => ['get', '/admin/jobs'],
        'translations.index' => ['get', '/admin/translations'],
        'users.index' => ['get', '/admin/users'],
    ];
});

it('redirects guests to login on admin routes', function (string $method, string $url): void {
    $this->{$method}($url)->assertRedirect(route('login'));
})->with('admin_guest_routes');

it('forbids non-admin users from admin routes', function (string $method, string $url): void {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->{$method}($url)->assertForbidden();
})->with('admin_non_admin_routes');

it('redirects guests to login for admin game-version POST routes', function (): void {
    $version = GameVersion::factory()->create();

    $this->post(route('admin.game-versions.set-default', $version))->assertRedirect(route('login'));
    $this->post(route('admin.game-versions.hide', $version))->assertRedirect(route('login'));
    $this->post(route('admin.game-versions.show', GameVersion::factory()->create(['is_hidden' => true])))->assertRedirect(route('login'));
});

it('forbids non-admin users from admin game-version POST routes', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    $version = GameVersion::factory()->create();

    $this->actingAs($user)->post(route('admin.game-versions.set-default', $version))->assertForbidden();
    $this->actingAs($user)->post(route('admin.game-versions.hide', $version))->assertForbidden();
    $this->actingAs($user)->post(route('admin.game-versions.show', GameVersion::factory()->create(['is_hidden' => true])))->assertForbidden();
});

it('redirects guests to login for admin jobs DELETE routes', function (): void {
    $this->delete(route('admin.jobs.destroy', 1))->assertRedirect(route('login'));
    $this->delete(route('admin.jobs.truncate'))->assertRedirect(route('login'));
});

it('forbids non-admin users from admin jobs DELETE routes', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->delete(route('admin.jobs.destroy', 1))->assertForbidden();
    $this->actingAs($user)->delete(route('admin.jobs.truncate'))->assertForbidden();
});

it('redirects guests to login for admin translation edit/update routes', function (): void {
    $commLink = CommLink::factory()->create();

    $this->get(route('admin.translations.edit', ['type' => 'comm-link', 'id' => $commLink->cig_id]))->assertRedirect(route('login'));
    $this->put(route('admin.translations.update', ['type' => 'comm-link', 'id' => $commLink->cig_id]))->assertRedirect(route('login'));
});

it('forbids non-admin users from admin translation edit/update routes', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    $commLink = CommLink::factory()->create();

    $this->actingAs($user)->get(route('admin.translations.edit', ['type' => 'comm-link', 'id' => $commLink->cig_id]))->assertForbidden();
    $this->actingAs($user)->put(route('admin.translations.update', ['type' => 'comm-link', 'id' => $commLink->cig_id]))->assertForbidden();
});

it('redirects guests to login for admin user destroy route', function (): void {
    $targetUser = User::factory()->create();

    $this->delete(route('admin.users.destroy', $targetUser))->assertRedirect(route('login'));
    $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
});

it('forbids non-admin users from admin user destroy route', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    $targetUser = User::factory()->create();

    $this->actingAs($user)->delete(route('admin.users.destroy', $targetUser))->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
});