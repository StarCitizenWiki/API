<?php

declare(strict_types=1);

use App\Jobs\Game\ImportStarmapData;
use App\Models\Game\GameVersion;
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

it('rewrites the row only when data actually changes', function (): void {
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
