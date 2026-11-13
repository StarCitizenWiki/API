<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->version = GameVersion::factory()->create(['is_default' => false]);
    $this->manufacturer = Manufacturer::factory()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);
});

function dispatchVehicleImport(int $versionId, array $payload, string $file = 'ships/test.json'): void
{
    Storage::disk('scunpacked')->put($file, json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($versionId, $file))->handle();
}

it('does not create vehicle data when its batch is cancelled', function (): void {
    $payload = ['UUID' => fake()->uuid(), 'ClassName' => 'TEST', 'Name' => 'Test', 'Manufacturer' => ['UUID' => $this->manufacturer->uuid]];
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    $job = new ImportVehicleData($this->version->id, 'ships/test.json');
    [$job] = $job->withFakeBatch(cancelledAt: now()->toImmutable());

    $job->handle();

    expect(VehicleData::query()->count())->toBe(0);
});

it('runs the import writes inside a transaction', function (): void {
    $baseline = DB::transactionLevel();
    $writes = [];
    $events = [];
    Event::listen(TransactionBeginning::class, function () use (&$events): void {
        $events[] = 'begin';
    });
    Event::listen(TransactionCommitted::class, function () use (&$events): void {
        $events[] = 'commit';
    });
    DB::listen(function ($query) use (&$writes): void {
        if (preg_match('/^(insert|update|delete)\b/i', $query->sql)) {
            $writes[] = [$query->sql, $query->connection->transactionLevel()];
        }
    });

    $payload = vehiclePayload($this->manufacturer->uuid);
    $payload['UUID'] = fake()->uuid();
    dispatchVehicleImport($this->version->id, $payload);

    expect($events)->toContain('begin', 'commit');
    $matching = array_values(array_filter($writes, fn ($write) => str_contains($write[0], 'game_vehicles')));
    expect($matching)->not->toBeEmpty();
    foreach ($matching as $write) {
        expect($write[1])->toBeGreaterThan($baseline);
    }
    $matching = array_values(array_filter($writes, fn ($write) => str_contains($write[0], 'game_vehicle_data')));
    expect($matching)->not->toBeEmpty();
    foreach ($matching as $write) {
        expect($write[1])->toBeGreaterThan($baseline);
    }
});

it('creates vehicle data on first import', function (): void {
    $payload = vehiclePayload($this->manufacturer->uuid);

    dispatchVehicleImport($this->version->id, $payload);

    $vehicle = Vehicle::query()->where('uuid', $payload['UUID'])->first();
    expect($vehicle)->not->toBeNull();

    $vehicleData = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $this->version->id)
        ->first();

    expect($vehicleData)->not->toBeNull()
        ->and($vehicleData->name)->toBe('Test Ship')
        ->and($vehicleData->career)->toBe('Combat')
        ->and($vehicleData->is_spaceship)->toBeTrue()
        ->and($vehicleData->data)->toBeArray();
});

it('persists the row when source data changes', function (): void {
    $payload = vehiclePayload($this->manufacturer->uuid);

    dispatchVehicleImport($this->version->id, $payload);

    // Simulate a genuine data change in the source ship.
    $payload['Name'] = 'Updated Ship Name';
    dispatchVehicleImport($this->version->id, $payload);

    $vehicleData = VehicleData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    // The guard must let a genuine change through and persist it.
    expect($vehicleData->name)->toBe('Updated Ship Name');
});

/**
 * Report whether the job's static vehicle cache still holds the given uuid.
 */
function vehicleImportCacheHas(string $uuid): bool
{
    $cache = new ReflectionProperty(ImportVehicleData::class, 'vehicleCache');

    return array_key_exists($uuid, (array) $cache->getValue());
}

/**
 * Throw once when the import writes the given table (and column), to inject a
 * failure at a specific vehicle chain stage.
 */
function failVehicleImportAtWrite(string $table, ?string $column = null): void
{
    $armed = true;

    DB::listen(function ($query) use ($table, $column, &$armed): void {
        if (! $armed) {
            return;
        }
        if (preg_match('/^(insert|update|delete)\b/i', $query->sql) !== 1 || ! str_contains($query->sql, "\"{$table}\"")) {
            return;
        }
        if ($column !== null && ! str_contains($query->sql, "\"{$column}\"")) {
            return;
        }
        $armed = false;

        throw new RuntimeException("injected {$table} write failure");
    });
}

it('recreates a new parent vehicle on a same-process retry after a rolled-back import', function (): void {
    $vehicleUuid = '17171717-1717-1717-1717-171717171717';
    $payload = vehiclePayload('99999999-9999-9999-9999-999999999999');
    $payload['UUID'] = $vehicleUuid;

    // The unknown manufacturer throws after the parent vehicle was already created in the transaction.
    expect(fn () => dispatchVehicleImport($this->version->id, $payload, 'ships/retry-new.json'))
        ->toThrow(RuntimeException::class);

    expect(Vehicle::query()->count())->toBe(0)
        ->and(VehicleData::query()->count())->toBe(0)
        ->and(vehicleImportCacheHas($vehicleUuid))->toBeFalse();

    Manufacturer::factory()->create([
        'uuid' => '99999999-9999-9999-9999-999999999999',
        'name' => 'Retry Manufacturer',
        'code' => 'RTR',
    ]);

    dispatchVehicleImport($this->version->id, $payload, 'ships/retry-new.json');

    $vehicle = Vehicle::query()->where('uuid', $vehicleUuid)->firstOrFail();
    expect($vehicle->slug)->not->toBeNull()
        ->and(VehicleData::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('game_version_id', $this->version->id)
            ->exists())->toBeTrue();
});

it('rolls back installed-item pivots and drops the uncommitted cached vehicle when a late stage fails', function (): void {
    $vehicleUuid = '18181818-1818-1818-1818-181818181818';
    $vehicle = Vehicle::factory()->create(['uuid' => $vehicleUuid, 'slug' => null, 'display_name_slug' => null]);

    $item = Item::factory()->create(['uuid' => '19191919-1919-1919-1919-191919191919']);
    ItemData::factory()->create(['item_id' => $item->id, 'game_version_id' => $this->version->id]);

    $payload = vehiclePayload($this->manufacturer->uuid);
    $payload['UUID'] = $vehicleUuid;
    $payload['Loadout'] = [['UUID' => $item->uuid]];

    // Fail while persisting the display name slug, after the data upsert, pivot sync, and slug writes.
    failVehicleImportAtWrite('game_vehicles', 'display_name_slug');

    Storage::disk('scunpacked')->put('ships/retry-existing.json', json_encode($payload, JSON_THROW_ON_ERROR));

    expect(fn () => (new ImportVehicleData($this->version->id, 'ships/retry-existing.json'))->handle())
        ->toThrow(RuntimeException::class, 'injected game_vehicles write failure');

    expect(VehicleData::query()->count())->toBe(0)
        ->and(DB::table('game_item_data_vehicle_data')->count())->toBe(0)
        ->and($vehicle->refresh()->slug)->toBeNull()
        ->and(vehicleImportCacheHas($vehicleUuid))->toBeFalse();

    dispatchVehicleImport($this->version->id, $payload, 'ships/retry-existing.json');

    expect($vehicle->refresh()->slug)->toBe('test-ship')
        ->and(VehicleData::query()->where('vehicle_id', $vehicle->id)->count())->toBe(1)
        ->and(DB::table('game_item_data_vehicle_data')->count())->toBe(1);
});

/**
 * Build a minimal, valid vehicle payload.
 */
function vehiclePayload(string $manufacturerUuid): array
{
    return [
        'UUID' => '22222222-2222-2222-2222-222222222222',
        'ClassName' => 'TEST_SHIP',
        'Name' => 'Test Ship',
        'Career' => 'Combat',
        'Role' => 'Fighter',
        'IsVehicle' => false,
        'IsGravlev' => false,
        'IsSpaceship' => true,
        'Size' => 2,
        'Manufacturer' => [
            'UUID' => $manufacturerUuid,
            'Name' => 'Test Manufacturer',
        ],
    ];
}
