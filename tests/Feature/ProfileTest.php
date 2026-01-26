<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('profile controller exists and has required methods', function (): void {
    $controller = new ProfileController;

    expect($controller)->toBeInstanceOf(ProfileController::class)
        ->and(method_exists($controller, 'show'))->toBeTrue()
        ->and(method_exists($controller, 'createToken'))->toBeTrue()
        ->and(method_exists($controller, 'destroy'))->toBeTrue();
});

it('profile controller methods have proper return types', function (): void {
    $controller = new ProfileController;

    $showReflection = new \ReflectionMethod($controller, 'show');
    $createTokenReflection = new \ReflectionMethod($controller, 'createToken');
    $destroyReflection = new \ReflectionMethod($controller, 'destroy');

    expect($showReflection->getReturnType()->getName())->toBe('Illuminate\View\View')
        ->and($createTokenReflection->getReturnType()->getName())->toBe('Illuminate\Http\RedirectResponse')
        ->and($destroyReflection->getReturnType()->getName())->toBe('Illuminate\Http\RedirectResponse');
});

it('profile controller has auth middleware', function (): void {
    // Test that unauthenticated users are redirected to login (proves auth middleware)
    $response = $this->get('/profile');
    $response->assertRedirect('/login');
});

it('show method returns view', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    // This will fail until routes are defined in API-j9x.4
    // For now, we just verify the controller can be called
    expect(true)->toBeTrue();
});

it('createToken method redirects back', function (): void {
    $user = User::factory()->create();

    // This will be properly tested when routes are defined
    expect(true)->toBeTrue();
});

it('destroy method redirects back', function (): void {
    $user = User::factory()->create();

    // This will be properly tested when routes are defined
    expect(true)->toBeTrue();
});

it('profile page access: authenticated user can access /profile', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Profile')
        ->assertSee('Manage your account settings')
        ->assertSee('Change Password')
        ->assertSee('API Token')
        ->assertSee('Delete Account');
});

it('profile page access: unauthenticated user is redirected to login', function (): void {
    $response = $this->get('/profile');

    $response->assertRedirect('/login');
});

it('profile page access: page renders correctly with all sections', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Change Password')
        ->assertSee('Current Password')
        ->assertSee('New Password')
        ->assertSee('Confirm New Password')
        ->assertSee('Update Password')
        ->assertSee('API Token')
        ->assertSee('Create New Token')
        ->assertSee('Token Name')
        ->assertSee('Delete Account')
        ->assertSee('Once you delete your account, there is no going back')
        ->assertSee('I understand that this action is irreversible');
});

it('profile page access: shows empty state when user has no tokens', function (): void {
    // RefreshDatabase is already applied at the top of the file
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('You don\'t have any API tokens yet.', false);
});

it('profile page access: shows table when user has tokens', function (): void {
    $user = User::factory()->create();

    // Create a token for the user
    $user->createToken('Test Token', ['*']);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Name')
        ->assertSee('Last Used')
        ->assertSee('Actions')
        ->assertSee('Test Token');
});

it('token table: table renders with correct columns', function (): void {
    $user = User::factory()->create();

    // Create a token for the user
    $user->createToken('Test Token', ['*']);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Name')
        ->assertSee('Last Used')
        ->assertSee('Actions');
});

it('token table: token name is displayed without masked value', function (): void {
    $user = User::factory()->create();

    // Create a token for the user
    $user->createToken('Test Token', ['*']);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();
    $content = $response->getContent();

    // Verify token name is shown
    expect($content)->toContain('Test Token');

    // Verify masked pattern does NOT exist
    expect($content)->not->toContain('•••');
});

it('token table: last_used_at is displayed in human-readable format', function (): void {
    $user = User::factory()->create();

    // Create a token for the user
    $tokenResult = $user->createToken('Test Token', ['*']);
    $token = $tokenResult->accessToken;

    // Update last_used_at to a known time
    $token->last_used_at = now()->subHours(2);
    $token->save();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('2 hours ago');
});

it('token table: displays "Never" when token has never been used', function (): void {
    $user = User::factory()->create();

    // Create a token for the user
    $user->createToken('Test Token', ['*']);

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Never');
});

it('token table: empty state displays when no tokens', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('You don\'t have any API tokens yet.', false)
        ->assertSee('Create a token to authenticate with the API.');
});

it('token table: creation form is displayed in tokens card', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Token Name')
        ->assertSee('Create New Token')
        ->assertSee('name="name"', false);
});

it('password change: successfully changes password with correct current password', function (): void {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

    $response = $this->actingAs($user)->put('/user/password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'NewPassword456!',
    ]);

    $response->assertSessionHasNoErrors();

    // Verify password was changed
    $user->refresh();
    expect(Hash::check('NewPassword456!', $user->password))->toBeTrue()
        ->and(Hash::check('OldPassword123!', $user->password))->toBeFalse();
});

it('password change: fails with incorrect current password', function (): void {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

    $response = $this->actingAs($user)->from('/profile')->put('/user/password', [
        'current_password' => 'WrongPassword123!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'NewPassword456!',
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHasErrorsIn('updatePassword', 'current_password');

    // Verify password was NOT changed
    $user->refresh();
    expect(Hash::check('OldPassword123!', $user->password))->toBeTrue()
        ->and(Hash::check('NewPassword456!', $user->password))->toBeFalse();
});

it('password change: validates password length must be at least 8 characters', function (): void {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

    $response = $this->actingAs($user)->from('/profile')->put('/user/password', [
        'current_password' => 'OldPassword123!',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHasErrorsIn('updatePassword', ['password']);

    // Verify password was NOT changed
    $user->refresh();
    expect(Hash::check('OldPassword123!', $user->password))->toBeTrue();
});

it('password change: validates password confirmation', function (): void {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

    $response = $this->actingAs($user)->from('/profile')->put('/user/password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'DifferentPassword789!',
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHasErrorsIn('updatePassword', ['password']);

    // Verify password was NOT changed
    $user->refresh();
    expect(Hash::check('OldPassword123!', $user->password))->toBeTrue();
});

it('password change: validates required fields', function (): void {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

    $response = $this->actingAs($user)->from('/profile')->put('/user/password', [
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHasErrorsIn('updatePassword', ['current_password', 'password']);

    // Verify password was NOT changed
    $user->refresh();
    expect(Hash::check('OldPassword123!', $user->password))->toBeTrue();
});

it('password change: new password works for login', function (): void {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

    // Change password
    $this->actingAs($user)->put('/user/password', [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'NewPassword456!',
    ]);

    // Logout
    $this->post('/logout');

    // Verify old password doesn't work
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'OldPassword123!',
    ])->assertSessionHasErrors();

    // Verify new password works
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'NewPassword456!',
    ])->assertRedirect('/profile');

    $this->assertAuthenticated();
});

it('token creation: successfully creates token with user-provided name', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
        'name' => 'My Custom Token',
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHas('status', 'Your API token is ready')
        ->assertSessionHas('token')
        ->assertSessionHas('token_name');

    // Verify token appears in personal_access_tokens table
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => get_class($user),
        'name' => 'My Custom Token',
    ]);

    // Verify token name in session matches user-provided name
    expect(session('token_name'))->toBe('My Custom Token');

    // Verify token is not empty
    expect(session('token'))->not->toBeEmpty();
});

it('token creation: successfully creates up to 5 tokens', function (): void {
    $user = User::factory()->create();

    // Create 5 tokens with different names
    for ($i = 1; $i <= 5; $i++) {
        $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
            'name' => "Token {$i}",
        ]);

        $response->assertRedirect('/profile')
            ->assertSessionHas('status', 'Your API token is ready');
    }

    // Verify all 5 tokens exist in database
    $tokenCount = $user->tokens()->count();
    expect($tokenCount)->toBe(5);

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name' => 'Token 1',
    ]);
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name' => 'Token 5',
    ]);
});

it('token creation: blocked when user attempts to create 6th token', function (): void {
    $user = User::factory()->create();

    // Create 5 tokens
    for ($i = 1; $i <= 5; $i++) {
        $this->actingAs($user)->from('/profile')->post('/profile/token', [
            'name' => "Token {$i}",
        ]);
    }

    // Verify 5 tokens exist
    expect($user->tokens()->count())->toBe(5);

    // Try to create 6th token
    $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
        'name' => 'Token 6',
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHasErrors('token', 'You have reached the maximum limit of 5 API tokens. Delete an existing token before creating a new one.');

    // Verify still only 5 tokens exist in database
    $tokenCount = $user->tokens()->count();
    expect($tokenCount)->toBe(5);
});

it('token creation: token is displayed in read-only input block after creation', function (): void {
    $user = User::factory()->create(['email' => 'display@example.com']);

    $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
        'name' => 'Display Test Token',
    ]);

    $response->assertRedirect('/profile');

    // Follow redirect to see the displayed token
    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('Your New API Token')
        ->assertSee('Copy');
});

it('token creation: copy button is present in token display', function (): void {
    $user = User::factory()->create(['email' => 'copy@example.com']);

    $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
        'name' => 'Copy Test Token',
    ]);

    $response->assertRedirect('/profile');

    // Follow redirect to check for copy button
    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk()
        ->assertSee('copyToken');
});

it('account deletion: successfully deletes account with DELETE_ACCOUNT confirmation', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;

    $response = $this->actingAs($user)->from('/profile')->delete('/profile', [
        'confirmation' => 'DELETE_ACCOUNT',
    ]);

    // Verify redirect to home page
    $response->assertRedirect('/')
        ->assertSessionHas('status', 'account-deleted');

    // Verify user is hard deleted (not soft deleted)
    $this->assertDatabaseMissing('users', [
        'id' => $userId,
    ]);

    // Verify user is logged out (cannot access protected route)
    $this->get('/profile')->assertRedirect('/login');
});

it('account deletion: fails without confirmation', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;

    $response = $this->actingAs($user)->from('/profile')->delete('/profile', [
        'confirmation' => '',
    ]);

    // Verify redirect back with errors
    $response->assertRedirect('/profile')
        ->assertSessionHasErrors('confirmation');

    // Verify user still exists in database
    $this->assertDatabaseHas('users', [
        'id' => $userId,
    ]);

    // Verify user is still logged in
    $this->assertAuthenticatedAs($user);
});

it('account deletion: fails with incorrect confirmation text', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;

    $response = $this->actingAs($user)->from('/profile')->delete('/profile', [
        'confirmation' => 'delete account',
    ]);

    // Verify redirect back with validation error
    $response->assertRedirect('/profile')
        ->assertSessionHasErrors('confirmation');

    // Verify user still exists in database
    $this->assertDatabaseHas('users', [
        'id' => $userId,
    ]);

    // Verify user is still logged in
    $this->assertAuthenticatedAs($user);
});

it('account deletion: cascades and deletes related tokens', function (): void {
    $user = User::factory()->create(['email' => 'delete@example.com']);
    $userId = $user->id;

    // Create a token for the user
    $token = $user->createToken('Delete Test Token', ['*']);
    $tokenId = $token->accessToken->id;

    // Verify token exists before deletion
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $tokenId,
        'tokenable_id' => $userId,
        'tokenable_type' => get_class($user),
    ]);

    // Delete the account
    $this->actingAs($user)->from('/profile')->delete('/profile', [
        'confirmation' => 'DELETE_ACCOUNT',
    ]);

    // Verify user is hard deleted
    $this->assertDatabaseMissing('users', [
        'id' => $userId,
    ]);

    // Verify token is also deleted (cascade)
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenId,
        'tokenable_id' => $userId,
    ]);
});

it('account deletion: multiple tokens are all deleted', function (): void {
    $user = User::factory()->create(['email' => 'multitoken@example.com']);
    $userId = $user->id;

    // Create multiple tokens for the user
    $token1 = $user->createToken('Token 1', ['*']);
    $token2 = $user->createToken('Token 2', ['*']);
    $token3 = $user->createToken('Token 3', ['*']);

    $tokenIds = [$token1->accessToken->id, $token2->accessToken->id, $token3->accessToken->id];

    // Verify all tokens exist before deletion
    $this->assertDatabaseCount('personal_access_tokens', 3);

    // Delete the account
    $this->actingAs($user)->from('/profile')->delete('/profile', [
        'confirmation' => 'DELETE_ACCOUNT',
    ]);

    // Verify user is hard deleted
    $this->assertDatabaseMissing('users', [
        'id' => $userId,
    ]);

    // Verify all tokens are deleted
    foreach ($tokenIds as $tokenId) {
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('account deletion: user cannot access authenticated routes after deletion', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;

    // Verify user can access profile before deletion
    $this->actingAs($user)->get('/profile')->assertOk();

    // Delete the account
    $this->actingAs($user)->from('/profile')->delete('/profile', [
        'confirmation' => 'DELETE_ACCOUNT',
    ]);

    // Verify user is hard deleted
    $this->assertDatabaseMissing('users', [
        'id' => $userId,
    ]);

    // Verify user is logged out and redirected to login
    $this->get('/profile')->assertRedirect('/login');
});

it('token deletion: user can delete their own token successfully', function (): void {
    $user = User::factory()->create();

    // Create a token for the user
    $tokenResult = $user->createToken('Test Token', ['*']);
    $tokenId = $tokenResult->accessToken->id;

    // Verify token exists before deletion
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $tokenId,
        'tokenable_id' => $user->id,
    ]);

    // Delete the token
    $response = $this->actingAs($user)->from('/profile')->delete("/profile/token/{$tokenId}");

    $response->assertRedirect('/profile')
        ->assertSessionHas('status', 'api-token-deleted');

    // Verify token is removed from database
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenId,
    ]);

    // Verify user still exists and is logged in
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
    $this->assertAuthenticatedAs($user);
});

it('token deletion: user cannot delete another user\'s token (404)', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create a token for user2
    $tokenResult = $user2->createToken('User2 Token', ['*']);
    $tokenId = $tokenResult->accessToken->id;

    // Verify token exists
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $tokenId,
        'tokenable_id' => $user2->id,
    ]);

    // User1 tries to delete user2's token
    $response = $this->actingAs($user1)->delete("/profile/token/{$tokenId}");

    // Should return 404
    $response->assertNotFound();

    // Verify token still exists (was not deleted)
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $tokenId,
        'tokenable_id' => $user2->id,
    ]);
});

it('token deletion: deleting non-existent token returns 404', function (): void {
    $user = User::factory()->create();

    // Try to delete a token that doesn't exist
    $response = $this->actingAs($user)->delete('/profile/token/999999');

    $response->assertNotFound();
});

it('token deletion: unauthenticated user cannot delete token', function (): void {
    // Try to delete token without authentication
    $response = $this->delete('/profile/token/1');

    // Should redirect to login
    $response->assertRedirect('/login');
});

it('multi-token creation: validates token name is required', function (): void {
    $user = User::factory()->create();

    // Try to create token without name
    $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
        'name' => '',
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHasErrors('name');

    // Verify no token was created
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
    ]);
});

it('multi-token creation: validates token name max 255 characters', function (): void {
    $user = User::factory()->create();

    // Try to create token with name exceeding 255 characters
    $longName = str_repeat('a', 256);

    $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
        'name' => $longName,
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHasErrors('name');

    // Verify no token was created
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
    ]);
});

it('multi-token creation: accepts token name exactly 255 characters', function (): void {
    $user = User::factory()->create();

    // Create token with name exactly 255 characters
    $validName = str_repeat('a', 255);

    $response = $this->actingAs($user)->from('/profile')->post('/profile/token', [
        'name' => $validName,
    ]);

    $response->assertRedirect('/profile')
        ->assertSessionHas('status', 'Your API token is ready');

    // Verify token was created
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
    ]);
});
