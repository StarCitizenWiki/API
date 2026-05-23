<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Testing\TestResponse;
use Symfony\Component\DomCrawler\Crawler;

function gameVersionCrawler(TestResponse $response): Crawler
{
    return new Crawler($response->getContent());
}

function availableGameVersionCodes(TestResponse $response): array
{
    return collect(gameVersionCrawler($response)->filter('select[name="version"] option')->each(
        fn (Crawler $option): ?string => $option->attr('value')
    ))
        ->filter()
        ->unique()
        ->values()
        ->all();
}

function selectedGameVersionCodes(TestResponse $response): array
{
    return collect(gameVersionCrawler($response)->filter('select[name="version"] option[selected]')->each(
        fn (Crawler $option): ?string => $option->attr('value')
    ))
        ->filter()
        ->unique()
        ->values()
        ->all();
}

function assertSelectedGameVersion(
    TestResponse $response,
    string $expectedVersionCode,
    ?array $expectedVersionCodes = null
): void {
    $response->assertSuccessful();

    expect(selectedGameVersionCodes($response))->toBe([$expectedVersionCode]);

    if ($expectedVersionCodes === null) {
        return;
    }

    expect(availableGameVersionCodes($response))->toBe($expectedVersionCodes);
}

it('does not persist the default game version in session for web requests', function (): void {
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
    assertSelectedGameVersion($response, $defaultVersion->code, [$defaultVersion->code, '3.24.0']);
    $this->assertNull(session('game_version_code'));
});

it('stores the requested game version from the query string across subsequent web requests', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.24.0',
        'is_default' => true,
    ]);

    $requestedVersion = GameVersion::factory()->create([
        'code' => '4.0.0-PTU',
        'is_default' => false,
    ]);

    $response = $this->get(route('home', ['version' => strtolower($requestedVersion->code)]));

    $response->assertSuccessful();
    assertSelectedGameVersion($response, $requestedVersion->code, [$defaultVersion->code, $requestedVersion->code]);
    $this->assertNotSame($defaultVersion->code, session('game_version_code'));
    $this->assertSame($requestedVersion->code, session('game_version_code'));

    $nextResponse = $this->get(route('home'));

    $nextResponse->assertSuccessful();
    assertSelectedGameVersion($nextResponse, $requestedVersion->code, [$defaultVersion->code, $requestedVersion->code]);
    $this->assertSame($requestedVersion->code, session('game_version_code'));
});

it('does not persist the default when the requested version matches it', function (): void {
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
    assertSelectedGameVersion($response, $defaultVersion->code, [$defaultVersion->code, '3.24.0']);
    $this->assertNull(session('game_version_code'));
});

it('does not persist anything when the requested version is unknown', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.24.1',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '3.24.0',
        'is_default' => false,
    ]);

    $response = $this->get(route('home', ['version' => '9.99.9']));

    $response->assertSuccessful();
    assertSelectedGameVersion($response, $defaultVersion->code, [$defaultVersion->code, '3.24.0']);
    $this->assertNull(session('game_version_code'));
});

it('hides hidden versions from the selector', function (): void {
    $visibleVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
    ]);

    $hiddenVersion = GameVersion::factory()->create([
        'code' => '4.6.0-PTU.1',
        'is_hidden' => true,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    assertSelectedGameVersion($response, $visibleVersion->code, [$visibleVersion->code]);
});

it('shows a version hint banner when a non-default version is selected', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
    ]);

    $olderVersion = GameVersion::factory()->create([
        'code' => '4.6.0-LIVE.1',
        'is_default' => false,
    ]);

    $response = $this->get(route('home', ['version' => $olderVersion->code]));

    $response->assertSuccessful();
    $response->assertSeeText('You are viewing data from version');
    $response->assertSeeText($olderVersion->code);
});

it('does not show a version hint banner when the default version is selected', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
    ]);

    GameVersion::factory()->create([
        'code' => '4.6.0-LIVE.1',
        'is_default' => false,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertDontSeeText('You are viewing data from version');
});

it('keeps hidden versions in session while rendering the default visible version', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '4.7.0-LIVE.1',
        'is_default' => true,
    ]);

    $hiddenVersion = GameVersion::factory()->create([
        'code' => '4.6.0-PTU.1',
        'is_hidden' => true,
    ]);

    $response = $this->get(route('home', ['version' => strtolower($hiddenVersion->code)]));

    $response->assertSuccessful();
    assertSelectedGameVersion($response, $defaultVersion->code, [$defaultVersion->code]);
    $this->assertSame($hiddenVersion->code, session('game_version_code'));

    $nextResponse = $this->get(route('home'));

    $nextResponse->assertSuccessful();
    assertSelectedGameVersion($nextResponse, $defaultVersion->code, [$defaultVersion->code]);
    $this->assertSame($hiddenVersion->code, session('game_version_code'));
});
