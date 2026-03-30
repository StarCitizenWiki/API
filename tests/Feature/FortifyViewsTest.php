<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $csrfToken = 'fortify-views-csrf-token';

    $this->withSession(['_token' => $csrfToken])
        ->withHeader('X-CSRF-TOKEN', $csrfToken);
});

it('allows guests to access fortify auth routes', function (): void {
    $this->get(route('login'))->assertSuccessful();
    $this->get(route('register'))->assertSuccessful();
    $this->get(route('password.request'))->assertSuccessful();
    $this->get(route('password.reset', ['token' => 'reset-token']))->assertSuccessful();
    $this->get(route('two-factor.login'))->assertRedirectToRoute('login');
});

it('renders the confirm password view for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('password.confirm'))
        ->assertSuccessful();
});

it('redirects to profile after successful login', function (): void {
    $user = User::factory()->create([
        'password' => bcrypt('Password123!'),
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirectToRoute('profile');
    $this->assertAuthenticatedAs($user);
});

it('redirects authenticated users away from login to profile', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirectToRoute('profile');
});
