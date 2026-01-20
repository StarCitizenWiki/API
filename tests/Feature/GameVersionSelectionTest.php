<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class)
    ->beforeEach(function () {
        //
    });

it('stores selected version in session and redirects with query string', function () {
    $version = GameVersion::factory()->create([
        'code' => '3.24.1',
        'is_default' => true,
    ]);

    $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->post(route('game-version.select'), [
            'version' => $version->code,
            'redirect' => '/?foo=bar',
        ]);

    $response->assertRedirect(url()->query('/?foo=bar', ['version' => $version->code]));
    $this->assertSame($version->code, session('game_version_code'));
});

it('rejects unknown game versions', function () {
    $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->post(route('game-version.select'), [
            'version' => '9.99.9',
            'redirect' => '/',
        ]);

    $response->assertSessionHasErrors('version');
});

it('prevents external redirects', function () {
    $version = GameVersion::factory()->create([
        'code' => '3.24.2',
    ]);

    $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
        ->post(route('game-version.select'), [
            'version' => $version->code,
            'redirect' => 'https://example.com/phish',
        ]);

    $response->assertRedirect(url()->query(url('/'), ['version' => $version->code]));
});
