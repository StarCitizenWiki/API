<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Registration Feature Tests
 *
 * FORTIFY_ALLOW_REGISTRATION is a deployment-time configuration in Laravel Fortify architecture.
 * Changes to this setting require environment variable modifications and application restart/redeploy.
 * Runtime toggling of registration is not supported by Fortify's design.
 *
 * The disabled state (FORTIFY_ALLOW_REGISTRATION=false) should be verified through manual testing:
 * 1. Set FORTIFY_ALLOW_REGISTRATION=false in .env
 * 2. Restart application (php artisan serve or redeploy)
 * 3. Visit /register - should return 404
 * 4. Attempt POST to /register - should return 404
 * 5. Set FORTIFY_ALLOW_REGISTRATION=true, restart, and verify registration works
 */
uses(RefreshDatabase::class);

/**
 * @runInSeparateProcess
 *
 * @preserveGlobalState disabled
 */
it('displays registration page when registration is enabled', function (): void {
    putenv('FORTIFY_ALLOW_REGISTRATION=true');

    $response = $this->get('/register');

    $response->assertStatus(200)
        ->assertSee('Create account')
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Confirm password');
});

it('allows a new user to register with valid credentials', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('profile'));

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();

    $user = User::where('email', 'test@example.com')->first();
    expect($user->name)->toBe('Test User');
});

it('authenticates user after successful registration', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('profile'));
    $this->assertAuthenticated();
});

it('validates required fields', function (): void {
    $response = $this->post('/register', [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

it('validates email format', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('validates password confirmation', function (): void {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword123!',
    ]);

    $response->assertSessionHasErrors(['password']);
});

it('prevents duplicate email registration', function (): void {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors(['email']);

    expect(User::where('email', 'existing@example.com')->count())->toBe(1);
});
