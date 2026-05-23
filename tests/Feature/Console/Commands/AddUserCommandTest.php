<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

it('creates a user', function (): void {
    $this->artisan('user:add', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        '--password' => 'Password123!',
    ])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('User "jane@example.com" created.');

    $user = User::query()->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Jane Doe')
        ->and($user->email)->toBe('jane@example.com')
        ->and($user->is_admin)->toBeFalse()
        ->and(Hash::check('Password123!', $user->password))->toBeTrue();
});

it('can create an admin user', function (): void {
    $this->artisan('user:add', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        '--password' => 'Password123!',
        '--admin' => true,
    ])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('User "admin@example.com" created.');

    $user = User::query()->where('email', 'admin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeTrue();
});

it('fails when the email already exists', function (): void {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $this->artisan('user:add', [
        'name' => 'New User',
        'email' => 'existing@example.com',
        '--password' => 'Password123!',
    ])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('The email has already been taken.');

    expect(User::query()->count())->toBe(1);
});
