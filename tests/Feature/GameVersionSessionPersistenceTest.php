<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not persist the default game version in session for web requests', function () {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.24.1',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.0',
        'is_default' => false,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $this->assertNull(session('game_version_code'));
});

it('stores the requested game version from the query string', function () {
    GameVersion::factory()->create([
        'code' => '3.24.0',
        'is_default' => true,
    ]);

    $requestedVersion = GameVersion::factory()->create([
        'code' => '4.0.0-PTU',
        'is_default' => false,
    ]);

    $response = $this->get(route('home', ['version' => strtolower($requestedVersion->code)]));

    $response->assertSuccessful();
    $this->assertSame($requestedVersion->code, session('game_version_code'));
});

it('does not persist the default when the requested version matches it', function () {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.24.1',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.0',
        'is_default' => false,
    ]);

    $response = $this->get(route('home', ['version' => $defaultVersion->code]));

    $response->assertSuccessful();
    $this->assertNull(session('game_version_code'));
});

it('does not persist anything when the requested version is unknown', function () {
    GameVersion::factory()->create([
        'code' => '3.24.1',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.0',
        'is_default' => false,
    ]);

    $response = $this->get(route('home', ['version' => '9.99.9']));

    $response->assertSuccessful();
    $this->assertNull(session('game_version_code'));
});

it('hides hidden versions from the selector', function () {
    $visibleVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
    ]);

    $hiddenVersion = GameVersion::factory()->create([
        'code' => '4.6.0-PTU.1',
        'is_hidden' => true,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful()
        ->assertSeeText($visibleVersion->code)
        ->assertDontSeeText($hiddenVersion->code);
});

it('persists hidden requested versions from the query string', function () {
    GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
    ]);

    $hiddenVersion = GameVersion::factory()->create([
        'code' => '4.6.0-PTU.1',
        'is_hidden' => true,
    ]);

    $response = $this->get(route('home', ['version' => strtolower($hiddenVersion->code)]));

    $response->assertSuccessful();
    $this->assertSame($hiddenVersion->code, session('game_version_code'));
});

it('falls back to the default visible version in the selector when a hidden version is requested', function () {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
    ]);

    $hiddenVersion = GameVersion::factory()->create([
        'code' => '4.6.0-PTU.1',
        'is_hidden' => true,
    ]);

    $response = $this->get(route('home', ['version' => strtolower($hiddenVersion->code)]));

    $response->assertSuccessful()
        ->assertSee('<option value="'.$defaultVersion->code.'" selected>', false)
        ->assertDontSeeText($hiddenVersion->code);

    $this->assertSame($hiddenVersion->code, session('game_version_code'));
});
