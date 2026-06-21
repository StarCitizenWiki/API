<?php

declare(strict_types=1);

use App\Jobs\Game\ImportStarmapData;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->version = GameVersion::factory()->create();
});

function dispatchStarmapImport(int $versionId, array $payload, string $file = 'starmap.json'): void
{
    Storage::disk('scunpacked')->put($file, json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportStarmapData($versionId, $file, 'scunpacked'))->handle();
}

it('creates location data on first import', function (): void {
    $payload = starmapPayload();

    dispatchStarmapImport($this->version->id, $payload);

    $data = StarmapLocationData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    expect($data)->not->toBeNull()
        ->and($data->name)->toBe('Port Olisar');
});

it('persists the row when source data changes', function (): void {
    $payload = starmapPayload();

    dispatchStarmapImport($this->version->id, $payload);

    // Simulate a genuine data change in the source starmap file.
    $payload[0]['Name'] = 'Port Tressler';
    dispatchStarmapImport($this->version->id, $payload);

    $data = StarmapLocationData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    // The guard must let a genuine change through and persist it.
    expect($data->name)->toBe('Port Tressler');
});

describe('slug stability', function (): void {
    it('keeps slugs byte-identical across repeated imports of the same data', function (): void {
        $payload = starmapPayloadWithDuplicates();

        dispatchStarmapImport($this->version->id, $payload);
        $afterFirst = StarmapLocation::query()->orderBy('uuid')->pluck('slug', 'uuid')->all();

        // Re-import the EXACT same payload. Slugs must not drift.
        dispatchStarmapImport($this->version->id, $payload);
        $afterSecond = StarmapLocation::query()->orderBy('uuid')->pluck('slug', 'uuid')->all();

        expect($afterSecond)->toBe($afterFirst);
    });

    it('preserves an existing slug instead of bumping it to a suffixed variant', function (): void {
        $uniqueUuid = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        StarmapLocation::factory()->create([
            'uuid' => $uniqueUuid,
            'slug' => 'onyx-facility-s2b1',
        ]);

        $payload = starmapPayloadWithDuplicates();

        dispatchStarmapImport($this->version->id, $payload);

        // The pre-existing slug survives the import unchanged (no -2 drift).
        expect(StarmapLocation::where('uuid', $uniqueUuid)->value('slug'))->toBe('onyx-facility-s2b1');
    });
});

/**
 * Build a minimal, valid starmap location payload.
 *
 * @return list<array<string, mixed>>
 */
function starmapPayload(): array
{
    return [
        [
            'UUID' => '11111111-1111-1111-1111-111111111111',
            'Name' => 'Port Olisar',
            'Description' => 'A prominent space station.',
            'Type' => ['Name' => 'Manmade', 'Classification' => 'Manmade'],
            'Size' => 1.0,
            'IsScannable' => true,
            'BlockTravel' => false,
            'HideInStarmap' => false,
            'HideInWorld' => false,
            'OnlyShowWhenParentSelected' => false,
            'Jurisdiction' => ['Name' => 'UEE'],
            'Affiliation' => ['DisplayName' => 'United Empire of Earth'],
            'RespawnLocationType' => 'Hospital',
            'Amenities' => [],
        ],
    ];
}

/**
 * Build a payload mixing a unique location name with several entries that
 * share a name, exercising both the unique and the collision paths.
 *
 * @return list<array<string, mixed>>
 */
function starmapPayloadWithDuplicates(): array
{
    $base = [
        'Description' => 'A procedurally generated station.',
        'Type' => ['Name' => 'Manmade', 'Classification' => 'Manmade'],
        'Size' => 1.0,
        'IsScannable' => true,
        'BlockTravel' => false,
        'HideInStarmap' => false,
        'HideInWorld' => false,
        'OnlyShowWhenParentSelected' => false,
        'Jurisdiction' => ['Name' => 'UEE'],
        'Affiliation' => ['DisplayName' => 'United Empire of Earth'],
        'RespawnLocationType' => 'Hospital',
        'Amenities' => [],
    ];

    return [
        ['UUID' => '22222222-2222-2222-2222-222222222222', 'Name' => 'Onyx Facility S2B1'] + $base,
        ['UUID' => '33333333-3333-3333-3333-333333333333', 'Name' => 'QV Logistics Station'] + $base,
        ['UUID' => '44444444-4444-4444-4444-444444444444', 'Name' => 'QV Logistics Station'] + $base,
        ['UUID' => '55555555-5555-5555-5555-555555555555', 'Name' => 'QV Logistics Station'] + $base,
    ];
}
