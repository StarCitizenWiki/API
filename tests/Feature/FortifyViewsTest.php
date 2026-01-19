<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

it('renders fortify guest views', function () {
    $this->get(route('login'))->assertSuccessful();
    expect(Route::has('register'))
        ->toBe(in_array(Features::registration(), config('fortify.features', []), true));

    if (Route::has('register')) {
        $this->get(route('register'))->assertSuccessful();
    }
    $this->get(route('password.request'))->assertSuccessful();
    $this->get(route('password.reset', ['token' => 'reset-token']))->assertSuccessful();
    $this->get(route('two-factor.login'))->assertRedirect(route('login'));
});

it('renders the confirm password view for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('password.confirm'))
        ->assertSuccessful();
});

it('removes the register route when registration is disabled', function (): void {
    $previous = getenv('FORTIFY_ALLOW_REGISTRATION');

    putenv('FORTIFY_ALLOW_REGISTRATION=false');
    $this->refreshApplication();

    expect(Route::has('register'))->toBeFalse();

    if ($previous === false) {
        putenv('FORTIFY_ALLOW_REGISTRATION');
    } else {
        putenv('FORTIFY_ALLOW_REGISTRATION='.$previous);
    }

    $this->refreshApplication();
});
