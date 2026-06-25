<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $csrfToken = 'profile-feature-csrf-token';

    $this->withSession(['_token' => $csrfToken])
        ->withHeader('X-CSRF-TOKEN', $csrfToken);
});

it('redirects guests to login', function (): void {
    $response = $this->get(route('profile'));

    $response->assertRedirectToRoute('login');
});

it('renders the authenticated profile view with the current user token controls', function (): void {
    $user = User::factory()->create();
    $userToken = $user->createToken('Phase 1 Token', ['*'])->accessToken;

    $otherUser = User::factory()->create();
    $otherUserToken = $otherUser->createToken('Other User Token', ['*'])->accessToken;

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertOk()
        ->assertViewIs('profile')
        ->assertViewHas('tokens', function ($tokens) use ($userToken): bool {
            return $tokens->pluck('id')->all() === [$userToken->id];
        })
        ->assertSeeText('Profile')
        ->assertSeeText('API Token')
        ->assertSeeText('Change Password')
        ->assertSeeText('Delete Account');

    $response->assertSeeText($userToken->name)
        ->assertDontSeeText($otherUserToken->name);
});

it('creates a token and flashes its value', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from(route('profile'))->post(route('profile.token.create'), [
        'name' => 'Phase 1 Token',
    ]);

    $response->assertRedirectToRoute('profile')
        ->assertSessionHas('status', 'Your API token is ready')
        ->assertSessionHas('token_name', 'Phase 1 Token');

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => get_class($user),
        'name' => 'Phase 1 Token',
    ]);
    expect(session('token'))->toBeString()->not->toBeEmpty();
});

it('rejects creating a token when the user already has five tokens', function (): void {
    $user = User::factory()->create();

    collect(range(1, 5))->each(function (int $index) use ($user): void {
        $user->createToken("Token {$index}", ['*']);
    });

    $response = $this->actingAs($user)->from(route('profile'))->post(route('profile.token.create'), [
        'name' => 'Token 6',
    ]);

    $response->assertRedirectToRoute('profile')
        ->assertSessionHasErrors([
            'token' => 'You have reached the maximum limit of 5 API tokens. Delete an existing token before creating a new one.',
        ])
        ->assertSessionHasInput('name', 'Token 6');

    $this->assertDatabaseCount('personal_access_tokens', 5);
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => get_class($user),
        'name' => 'Token 6',
    ]);
});

it('rejects a missing token name', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from(route('profile'))->post(route('profile.token.create'), []);

    $response->assertRedirectToRoute('profile')
        ->assertSessionHasErrors(['name']);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => get_class($user),
    ]);
});

it('rejects token names longer than 255 characters', function (): void {
    $user = User::factory()->create();
    $tokenName = str_repeat('a', 256);

    $response = $this->actingAs($user)->from(route('profile'))->post(route('profile.token.create'), [
        'name' => $tokenName,
    ]);

    $response->assertRedirectToRoute('profile')
        ->assertSessionHasErrors(['name']);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => get_class($user),
        'name' => $tokenName,
    ]);
});

it('deletes the account when the confirmation matches', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;
    $token = $user->createToken('Delete Test Token', ['*'])->accessToken;

    $response = $this->actingAs($user)->from(route('profile'))->delete(route('profile.destroy'), [
        'confirm' => '1',
    ]);

    $response->assertRedirectToRoute('home')
        ->assertSessionHas('status', 'account-deleted');

    $this->assertDatabaseMissing('users', [
        'id' => $userId,
    ]);
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->id,
    ]);
    $this->assertGuest();
});

it('shows an empty token list when the user has no tokens', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertOk()
        ->assertViewIs('profile')
        ->assertViewHas('tokens', fn ($tokens): bool => $tokens->isEmpty())
        ->assertSeeText('Profile')
        ->assertSeeText("You don't have any API tokens yet.")
        ->assertSeeText('Create a token to authenticate with the API.')
        ->assertSeeText('Change Password')
        ->assertSeeText('Delete Account');
});

it('shows a last used value for used tokens', function (): void {
    $user = User::factory()->create();

    Carbon::setTestNow(Carbon::parse('2026-03-30 12:00:00'));

    try {
        $tokenResult = $user->createToken('Test Token', ['*']);
        $token = $tokenResult->accessToken;

        $token->last_used_at = Carbon::parse('2026-03-30 10:00:00');
        $token->save();

        $response = $this->actingAs($user)->get(route('profile'));

        $response->assertOk()
            ->assertViewIs('profile')
            ->assertViewHas('tokens', function ($tokens) use ($token): bool {
                $profileToken = $tokens->firstWhere('id', $token->id);

                return $profileToken !== null
                    && $profileToken->last_used_at !== null
                    && $profileToken->last_used_at->equalTo($token->last_used_at);
            });

        $lastUsedCellMatch = preg_match(
            sprintf('/data-testid="profile-token-last-used-%d"[^>]*>\s*(.*?)\s*<\/td>/s', $token->id),
            (string) $response->getContent(),
            $matches,
        );

        $lastUsedText = trim(strip_tags($matches[1] ?? ''));

        expect($lastUsedCellMatch)->toBe(1)
            ->and($lastUsedText)->not->toBeEmpty();
    } finally {
        Carbon::setTestNow();
    }
});

it('deletes a token owned by the authenticated user', function (): void {
    $user = User::factory()->create();
    $tokenResult = $user->createToken('Test Token', ['*']);
    $tokenId = $tokenResult->accessToken->id;

    $response = $this->actingAs($user)->from(route('profile'))->delete(route('profile.token.delete', $tokenId));

    $response->assertRedirectToRoute('profile')
        ->assertSessionHas('status', 'api-token-deleted');

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenId,
    ]);
    $this->assertAuthenticatedAs($user);
});

it('rejects deleting another user\'s token', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $tokenId = $otherUser->createToken('Other User Token', ['*'])->accessToken->id;

    $response = $this->actingAs($user)->delete(route('profile.token.delete', $tokenId));

    $response->assertNotFound();

    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $tokenId,
    ]);
});

it('returns 404 when deleting a missing token', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('profile.token.delete', 999999));

    $response->assertNotFound();
});

it('changes the password and allows the new password to be used', function (): void {
    $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

    $response = $this->actingAs($user)->put(route('user-password.update'), [
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'NewPassword456!',
    ]);

    $response->assertSessionHasNoErrors();

    $user->refresh();
    expect(Hash::check('NewPassword456!', $user->password))->toBeTrue()
        ->and(Hash::check('OldPassword123!', $user->password))->toBeFalse();

    $this->post(route('logout'));

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'OldPassword123!',
    ])->assertSessionHasErrors();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'NewPassword456!',
    ])->assertRedirectToRoute('profile');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid password update payloads: :dataset', function (
    array $payload,
    string|array $expectedErrors
): void {
    $user = User::factory()->create(['password' => bcrypt('OldPassword123!')]);

    $response = $this->actingAs($user)->from(route('profile'))->put(route('user-password.update'), $payload);

    $response->assertRedirectToRoute('profile')
        ->assertSessionHasErrorsIn('updatePassword', $expectedErrors);

    $user->refresh();
    expect(Hash::check('OldPassword123!', $user->password))->toBeTrue();
})->with([
    'incorrect current password' => [[
        'current_password' => 'WrongPassword123!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'NewPassword456!',
    ], 'current_password'],
    'password too short' => [[
        'current_password' => 'OldPassword123!',
        'password' => 'short',
        'password_confirmation' => 'short',
    ], ['password']],
    'password confirmation mismatch' => [[
        'current_password' => 'OldPassword123!',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'DifferentPassword789!',
    ], ['password']],
    'required fields missing' => [[
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ], ['current_password', 'password']],
]);

it('rejects invalid account deletion confirmations', function (array $payload, string $expectedMessage): void {
    $user = User::factory()->create();
    $userId = $user->id;

    $response = $this->actingAs($user)->from(route('profile'))->delete(route('profile.destroy'), $payload);

    $response->assertRedirectToRoute('profile')
        ->assertSessionHasErrors([
            'confirm' => $expectedMessage,
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $userId,
    ]);
    $this->assertAuthenticatedAs($user);
})->with([
    'missing confirmation' => [[], 'You must confirm account deletion.'],
    'unchecked confirmation' => [['confirm' => '0'], 'You must confirm account deletion.'],
]);
