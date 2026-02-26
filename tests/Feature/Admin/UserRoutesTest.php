<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

it('redirects guests to login on admin users index', function (): void {
    $response = $this->get(route('admin.users.index'));

    $response->assertRedirect(route('login'));
});

it('forbids non-admin users from admin users index', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)
        ->get(route('admin.users.index'));

    $response->assertForbidden();
});

it('allows admins to view users index with expected users in view data', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $firstExpectedUser = User::factory()->create();
    $secondExpectedUser = User::factory()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index'));

    $response->assertSuccessful();
    $response->assertViewIs('admin.users.index');
    $response->assertViewHas('users', function (LengthAwarePaginator $users) use ($admin, $firstExpectedUser, $secondExpectedUser): bool {
        $userIds = $users->getCollection()->pluck('id');

        return $userIds->contains($admin->id)
            && $userIds->contains($firstExpectedUser->id)
            && $userIds->contains($secondExpectedUser->id);
    });
});

it('redirects guests to login on admin user destroy', function (): void {
    $targetUser = User::factory()->create();

    $response = $this->delete(route('admin.users.destroy', $targetUser));

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
});

it('forbids non-admin users from deleting users', function (): void {
    $nonAdmin = User::factory()->create(['is_admin' => false]);
    $targetUser = User::factory()->create();

    $response = $this->actingAs($nonAdmin)
        ->delete(route('admin.users.destroy', $targetUser));

    $response->assertForbidden();
    $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
});

it('allows admins to delete another user and redirects with success flash', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $targetUser));

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('success', 'User deleted successfully.');
    $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
});
