<?php

declare(strict_types=1);

use App\Models\User;

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

it('shows the admin users index with visible rows and actions', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $firstExpectedUser = User::factory()->create();
    $secondExpectedUser = User::factory()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.users.index'));

    $response->assertSuccessful()
        ->assertSeeText('Users Management')
        ->assertSeeText('Total: 3')
        ->assertSeeText($admin->name)
        ->assertSeeText($admin->email)
        ->assertSeeText($firstExpectedUser->name)
        ->assertSeeText($firstExpectedUser->email)
        ->assertSeeText($secondExpectedUser->name)
        ->assertSeeText($secondExpectedUser->email)
        ->assertDontSee(route('admin.users.destroy', $admin), false)
        ->assertSee(route('admin.users.destroy', $firstExpectedUser), false)
        ->assertSee(route('admin.users.destroy', $secondExpectedUser), false);
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

it('prevents admins from deleting their own account', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin));

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('error', 'You cannot delete your own account.');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});
