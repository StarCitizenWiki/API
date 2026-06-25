<?php

declare(strict_types=1);

use App\Models\User;

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
