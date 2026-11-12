<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;

describe('session-versioned web cache control', function (): void {
    it('marks responses for a non-default session version private and skips shared cache tags', function (): void {
        GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.1',
            'is_default' => true,
        ]);

        $ptuVersion = GameVersion::factory()->create([
            'code' => '4.8.0-PTU.1',
            'is_default' => false,
        ]);

        // Seed the session, then request the plain URL that only differs by session state.
        $this->get(route('web.ship-matrix.vehicles.index', ['version' => $ptuVersion->code]));
        $this->assertSame($ptuVersion->code, session('game_version_code'));

        $response = $this->get(route('web.ship-matrix.vehicles.index'));

        $response->assertSuccessful();
        expect($response->headers->get('Cache-Control'))
            ->toContain('private')
            ->toContain('no-store');
        expect($response->headers->has('Cache-Tag'))->toBeFalse();
    });

    it('emits shared cache tags for requests without a session version', function (): void {
        GameVersion::factory()->create([
            'code' => '4.7.0-LIVE.1',
            'is_default' => true,
        ]);

        GameVersion::factory()->create([
            'code' => '4.8.0-PTU.1',
            'is_default' => false,
        ]);

        $response = $this->get(route('web.ship-matrix.vehicles.index'));

        $response->assertSuccessful()
            ->assertHeader('Cache-Tag', 'web, web-vehicles');
    });
});
