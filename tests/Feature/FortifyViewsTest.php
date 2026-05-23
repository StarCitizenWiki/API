<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Symfony\Component\DomCrawler\Crawler;

function fortifyViewCrawler(TestResponse $response): Crawler
{
    return new Crawler($response->getContent());
}

function assertFormActionAndInputs(TestResponse $response, string $action, array $inputs): void
{
    $form = fortifyViewCrawler($response)->filter(sprintf('form[action="%s"]', $action));

    expect($form->count())->toBe(1);

    foreach ($inputs as $input) {
        expect($form->filter(sprintf('[name="%s"]', $input))->count())->toBeGreaterThan(0);
    }
}

function assertFortifyLinkPresent(TestResponse $response, string $href, ?string $text = null): void
{
    $links = fortifyViewCrawler($response)->filter(sprintf('a[href="%s"]', $href));

    expect($links->count())->toBeGreaterThan(0);

    if ($text !== null) {
        $texts = $links->each(static fn (Crawler $link): string => trim($link->text()));

        expect($texts)->toContain($text);
    }
}

it('renders the login page for guests', function (): void {
    $response = $this->get(route('login'));

    $response->assertSuccessful()
        ->assertSeeText('Welcome back')
        ->assertSeeText('Sign in to continue.')
        ->assertSeeText('Sign in');

    assertFormActionAndInputs($response, route('login.store'), ['email', 'password']);
    assertFortifyLinkPresent($response, route('password.request'), 'Forgot?');
    assertFortifyLinkPresent($response, route('register'), 'Create an account');
});

it('renders the registration page for guests', function (): void {
    $response = $this->get(route('register'));

    $response->assertSuccessful()
        ->assertSeeText('Star Citizen Wiki API')
        ->assertSeeText('Create a new account.')
        ->assertSeeText('Create account');

    assertFormActionAndInputs($response, route('register.store'), ['name', 'email', 'password', 'password_confirmation']);
    assertFortifyLinkPresent($response, route('login'), 'Sign in');
});

it('renders the forgot password page for guests', function (): void {
    $response = $this->get(route('password.request'));

    $response->assertSuccessful()
        ->assertSeeText('Reset your password')
        ->assertSeeText('We will email you a reset link.')
        ->assertSeeText('Send reset link');

    assertFormActionAndInputs($response, route('password.email'), ['email']);
    assertFortifyLinkPresent($response, route('login'), 'Back to sign in');
});

it('redirects guests away from two-factor login to login', function (): void {
    $this->get(route('two-factor.login'))
        ->assertRedirectToRoute('login');
});

it('renders the reset password page for guests', function (): void {
    $response = $this->get(route('password.reset', ['token' => 'reset-token']));

    $response->assertSuccessful()
        ->assertSeeText('Choose a new password')
        ->assertSeeText('Secure your account with a new password.')
        ->assertSeeText('Update password');

    assertFormActionAndInputs($response, route('password.update'), ['token', 'email', 'password', 'password_confirmation']);

    $tokenInput = fortifyViewCrawler($response)->filter('input[name="token"]');

    expect($tokenInput->count())->toBe(1)
        ->and($tokenInput->attr('value'))->toBe('reset-token');
});

it('renders the confirm password view for authenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('password.confirm'));

    $response->assertSuccessful()
        ->assertSeeText('Confirm your password')
        ->assertSeeText('This action requires a fresh confirmation.')
        ->assertSeeText('Confirm');

    assertFormActionAndInputs($response, route('password.confirm.store'), ['password']);
});

it('redirects to profile after successful login', function (): void {
    $csrfToken = 'fortify-views-csrf-token';
    $user = User::factory()->create([
        'password' => bcrypt('Password123!'),
    ]);

    $response = $this->withSession(['_token' => $csrfToken])
        ->withHeader('X-CSRF-TOKEN', $csrfToken)
        ->post(route('login'), [
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

it('renders the profile page for authenticated users', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('profile'));

    $response->assertOk()
        ->assertViewIs('profile');

    assertFormActionAndInputs($response, route('profile.token.create'), ['name']);
    assertFormActionAndInputs($response, route('user-password.update'), ['current_password', 'password', 'password_confirmation']);
    assertFormActionAndInputs($response, route('profile.destroy'), ['confirm']);
});
