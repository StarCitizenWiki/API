<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\VersionDiff;

describe('show', function (): void {
    it('renders the changelog page', function (): void {
        $previousVersion = GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.88888888',
            'channel' => 'live',
            'released_at' => now()->subWeek(),
            'is_hidden' => false,
        ]);

        $version = GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.99999999',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
            'is_hidden' => false,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $previousVersion->id,
            'to_version_id' => $version->id,
        ]);

        $response = $this->get(route('web.changelog.show', $version->code));

        $response->assertSuccessful();
        $response->assertSeeText('Version Changelog');
        $response->assertSeeText($previousVersion->code);
        $response->assertSeeText($version->code);
    });

    it('shows older version link when an older version with diffs exists', function (): void {
        $oldestVersion = GameVersion::factory()->create([
            'code' => '4.6.0-LIVE.77777777',
            'channel' => 'live',
            'released_at' => now()->subMonth(),
            'is_hidden' => false,
        ]);

        $middleVersion = GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.88888888',
            'channel' => 'live',
            'released_at' => now()->subWeek(),
            'is_hidden' => false,
        ]);

        $newestVersion = GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.99999999',
            'channel' => 'live',
            'released_at' => now(),
            'is_hidden' => false,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $oldestVersion->id,
            'to_version_id' => $middleVersion->id,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $middleVersion->id,
            'to_version_id' => $newestVersion->id,
        ]);

        $response = $this->get(route('web.changelog.show', $newestVersion->code));

        $response->assertSuccessful();

        // "Older" link points to the adjacent version in the list (middle)
        $olderUrl = route('web.changelog.show', $middleVersion->code);
        $response->assertSee($olderUrl);
    });

    it('shows newer version link when a newer version with diffs exists', function (): void {
        $oldestVersion = GameVersion::factory()->create([
            'code' => '4.6.0-LIVE.77777777',
            'channel' => 'live',
            'released_at' => now()->subMonth(),
            'is_hidden' => false,
        ]);

        $middleVersion = GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.88888888',
            'channel' => 'live',
            'released_at' => now()->subWeek(),
            'is_hidden' => false,
        ]);

        $newestVersion = GameVersion::factory()->create([
            'code' => '4.8.0-LIVE.99999999',
            'channel' => 'live',
            'released_at' => now(),
            'is_hidden' => false,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $oldestVersion->id,
            'to_version_id' => $middleVersion->id,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $middleVersion->id,
            'to_version_id' => $newestVersion->id,
        ]);

        $response = $this->get(route('web.changelog.show', $middleVersion->code));

        $response->assertSuccessful();

        $newerUrl = route('web.changelog.show', $newestVersion->code);
        $response->assertSee($newerUrl);
    });

    it('does not show older link for the oldest version with diffs', function (): void {
        $hiddenOlder = GameVersion::factory()->create([
            'code' => '4.5.0-LIVE.66666666',
            'channel' => 'live',
            'released_at' => now()->subMonths(3),
            'is_hidden' => false,
        ]);

        $oldestVersion = GameVersion::factory()->create([
            'code' => '4.6.0-LIVE.77777777',
            'channel' => 'live',
            'released_at' => now()->subMonth(),
            'is_hidden' => false,
        ]);

        $newerVersion = GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.88888888',
            'channel' => 'live',
            'released_at' => now()->subWeek(),
            'is_hidden' => false,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $oldestVersion->id,
            'to_version_id' => $newerVersion->id,
        ]);

        $response = $this->get(route('web.changelog.show', $oldestVersion->code));

        $response->assertSuccessful();
        // $hiddenOlder has no diffs, so it should not appear as "older" in nav
        $response->assertDontSee(route('web.changelog.show', $hiddenOlder->code));
    });

    it('excludes versions without diffs from navigation', function (): void {
        $versionWithoutDiffs = GameVersion::factory()->create([
            'code' => '4.5.0-LIVE.66666666',
            'channel' => 'live',
            'released_at' => now()->subMonths(3),
            'is_hidden' => false,
        ]);

        $previousVersion = GameVersion::factory()->create([
            'code' => '4.6.0-LIVE.77777777',
            'channel' => 'live',
            'released_at' => now()->subMonth(),
            'is_hidden' => false,
        ]);

        $version = GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.88888888',
            'channel' => 'live',
            'released_at' => now()->subWeek(),
            'is_hidden' => false,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $previousVersion->id,
            'to_version_id' => $version->id,
        ]);

        $response = $this->get(route('web.changelog.show', $version->code));

        $response->assertSuccessful();
        // 4.5.0 has no diffs as to_version, so it should not appear as "older"
        $response->assertDontSee(route('web.changelog.show', $versionWithoutDiffs->code));
    });

    it('returns 404 for unknown version', function (): void {
        $this->get(route('web.changelog.show', 'nonexistent'))
            ->assertNotFound();
    });
});

describe('version persistence', function (): void {
    beforeEach(function (): void {
        $this->oldestVersion = GameVersion::factory()->create([
            'code' => '4.7.2-LIVE.77777777',
            'channel' => 'live',
            'released_at' => now()->subMonths(2),
            'is_hidden' => false,
        ]);

        $this->middleVersion = GameVersion::factory()->create([
            'code' => '4.8.2-LIVE.88888888',
            'channel' => 'live',
            'released_at' => now()->subMonth(),
            'is_hidden' => false,
        ]);

        $this->currentVersion = GameVersion::factory()->create([
            'code' => '4.9.0-LIVE.99999999',
            'channel' => 'live',
            'is_default' => true,
            'released_at' => now(),
            'is_hidden' => false,
        ]);

        VersionDiff::factory()->create([
            'from_version_id' => $this->oldestVersion->id,
            'to_version_id' => $this->middleVersion->id,
        ]);

        VersionDiff::factory()
            ->count(51)
            ->sequence(fn ($sequence) => ['entity_id' => $sequence->index + 1])
            ->create([
                'from_version_id' => $this->middleVersion->id,
                'to_version_id' => $this->currentVersion->id,
            ]);
    });

    it('does not redirect when paginating while a different version is persisted in session', function (): void {
        // Simulates having previously selected the middle version on some
        // ?version=... page, which stores it in the session.
        $this->withSession(['game_version_code' => $this->middleVersion->code]);

        $response = $this->get(route('web.changelog.show', [
            'version' => $this->currentVersion->code,
            'page' => 2,
        ]));

        // Regression: previously the session version was injected as ?version=,
        // triggering ChangelogController's canonicalization redirect, which
        // switched the version and dropped the page param.
        $response->assertSuccessful();
        $response->assertHeaderMissing('Location');
    });

    it('does not self-redirect when the path version matches the persisted session version', function (): void {
        $this->withSession(['game_version_code' => $this->middleVersion->code]);

        $response = $this->get(route('web.changelog.show', [
            'version' => $this->middleVersion->code,
        ]));

        // Regression: with path and session versions equal, the controller
        // redirected to the identical URL forever.
        $response->assertSuccessful();
        $response->assertHeaderMissing('Location');
    });
});
