<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('skips items and vehicles when flags are provided', function (): void {
    Storage::fake('scunpacked');
    Storage::disk('scunpacked')->put('manufacturers.json', json_encode([
        ['reference' => fake()->uuid(), 'name' => 'ACME', 'code' => 'AC'],
    ], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('tags.json', json_encode([
        fake()->uuid() => 'Tag 1',
    ], JSON_THROW_ON_ERROR));

    GameVersion::query()->create([
        'code' => '3.23.0',
        'channel' => 'PTU',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Bus::fake();

    Artisan::call('game:sync-data', [
        '--game-version' => '3.23.0',
        '--skip-items' => true,
        '--skip-vehicles' => true,
    ]);

    Bus::assertNothingBatched();
});
