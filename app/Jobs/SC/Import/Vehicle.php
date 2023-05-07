<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Vehicle\Hardpoint;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use JsonException;

/**
 * TODO: Refactor this behemoth :(
 */
class Vehicle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private function isNotIgnoredClass(string $class): bool
    {
        $tests = [
            'fw22nfz',
            'modifiers',
            'SM_TE',
            'Bombless',
            'BIS29',
            'Indestructible',
            'Prison',
            'NoCrimesAgainst',
            'Unmanned',
            'F7A_Mk1',
            'CINEMATIC_ONLY',
            'NO_CUSTOM',
            'Test',
        ];

        $isGood = true;

        foreach ($tests as $toTest) {
            $isGood = $isGood && stripos($class, $toTest) === false;
        }

        $isGood = $isGood && $class !== 'TEST_Boat';

        return $isGood;
    }

    public function handle(): void
    {
        try {
            $vehicles = File::get(storage_path('app/api/scunpacked-data/v2/ships.json'));
        } catch (FileNotFoundException $e) {
            $this->fail('ships.json not found. Did you clone scunpacked?');
            return;
        }

        try {
            $vehicles = json_decode($vehicles, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        // TODO: Yeah this needs to go
        //VehicleHardpoint::query()->truncate();

        collect($vehicles)->chunk(5)->each(function (Collection $chunk) use ($labels, $manufacturers) {
            $chunk
                ->filter(function (array $vehicle) {
                    return $this->isNotIgnoredClass($vehicle['ClassName']);
                })
                ->map(function (array $vehicle) {
                    $fileV2 = sprintf(
                        'api/scunpacked-data/v2/ships/%s-raw.json',
                        strtolower($vehicle['ClassName'])
                    );

                    try {
                        $rawData = File::get(storage_path(sprintf('app/%s', $fileV2)));

                        $vehicle['rawData'] = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);
                    } catch (FileNotFoundException|JsonException $e) {
                    }

                    $vehicle['filePath'] = sprintf(
                        'api/scunpacked-data/ships/%s.json',
                        strtolower($vehicle['ClassName'])
                    );

                    return $vehicle;
                })->each(function (array $vehicle) use ($labels, $manufacturers) {
                    if (!isset($vehicle['rawData']['Entity']['__ref'])) {
                        return;
                    }

                    /** @var \App\Models\SC\Vehicle\Vehicle $vehicleModel */
                    $vehicleModel = \App\Models\SC\Vehicle\Vehicle::updateOrCreate([
                        'item_uuid' => $vehicle['rawData']['Entity']['__ref'],
                    ], $this->getVehicleModelArray($vehicle) + ['class_name' => $vehicle['ClassName']]);

                    if (!$vehicleModel->item === null || !optional($vehicleModel->item)->exists) {
                        $itemParser = new \App\Services\Parser\StarCitizenUnpacked\Item(
                            $vehicle['filePath'],
                            $labels,
                            $manufacturers
                        );

                        $data = $itemParser->getData();
                        if ($data !== null) {
                            (new Item($data))->handle();
                        }
                    }

                    $vehicleModel->refresh();

                    if (Arr::get($vehicle, 'Inventory') !== null) {
                        $vehicleModel->item->container()->updateOrCreate([
                            'item_uuid' => $vehicle['rawData']['Entity']['__ref'],
                        ], [
                            'width' => Arr::get($vehicle, 'Inventory.x'),
                            'height' => Arr::get($vehicle, 'Inventory.y'),
                            'length' => Arr::get($vehicle, 'Inventory.z'),
                            'scu' => Arr::get($vehicle, 'Inventory.SCU'),
                            'unit' => Arr::get($vehicle, 'Inventory.unit'),
                        ]);
                    }

                    $this->createHardpoints($vehicleModel, $vehicle['rawData']);
                });
        });
    }

    public function getVehicleModelArray(array $vehicle): array
    {
        return [
            'item_uuid' => $vehicle['rawData']['Entity']['__ref'],

            'shipmatrix_id' => $this->tryGetShipmatrixIdForVehicle($vehicle)->id ?? 0,
            'name' => $vehicle['Name'],
            'career' => $vehicle['Career'],
            'role' => $vehicle['Role'],
            'is_ship' => (bool)$vehicle['IsSpaceship'],
            'size' => $vehicle['Size'],
            'width' => $vehicle['Width'],
            'height' => $vehicle['Height'],
            'length' => $vehicle['Length'],

            'crew' => $vehicle['Crew'],
            'weapon_crew' => $vehicle['WeaponCrew'],
            'operations_crew' => $vehicle['OperationsCrew'],
            'mass' => $vehicle['Mass'],

//            'scm_speed' => $this->numFormat($vehicle['FlightCharacteristics']['ScmSpeed']),
//            'max_speed' => $this->numFormat($vehicle['FlightCharacteristics']['MaxSpeed']),

            'zero_to_scm' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToScm']),
            'zero_to_max' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToMax']),

            'scm_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['ScmToZero']),
            'max_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['MaxToZero']),

//            'pitch' => $this->numFormat($vehicle['FlightCharacteristics']['Pitch']),
//            'yaw' => $this->numFormat($vehicle['FlightCharacteristics']['Yaw']),
//            'roll' => $this->numFormat($vehicle['FlightCharacteristics']['Roll']),

            'acceleration_main' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.Acceleration.Main', 0)),
            'acceleration_retro' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.Acceleration.Retro', 0)),
            'acceleration_vtol' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.Acceleration.Vtol', 0)),
            'acceleration_maneuvering' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.Acceleration.Maneuvering', 0)),

            'acceleration_g_main' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.AccelerationG.Main', 0)),
            'acceleration_g_retro' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.AccelerationG.Retro', 0)),
            'acceleration_g_vtol' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.AccelerationG.Vtol', 0)),
            'acceleration_g_maneuvering' => $this->numFormat(Arr::get($vehicle, 'FlightCharacteristics.AccelerationG.Maneuvering', 0)),

            'claim_time' => $this->numFormat($vehicle['Insurance']['StandardClaimTime'] ?? 0),
            'expedite_time' => $this->numFormat($vehicle['Insurance']['ExpeditedClaimTime'] ?? 0),
            'expedite_cost' => $this->numFormat($vehicle['Insurance']['ExpeditedCost'] ?? 0),
        ];
    }

    /**
     * As  some in-game ship names differ from the ship matrix, we try to catch this here
     * Currently an in-game ship needs to have an accompanying ship-matrix entry
     *
     * @param array $vehicle
     * @return Builder|Model|object|null
     */
    private function tryGetShipmatrixIdForVehicle(array $vehicle)
    {
        $nameFix = explode(' ', $vehicle['Name']);
        array_shift($nameFix);
        $name = implode(' ', $nameFix ?? $vehicle['Name']);
        $nameDashed = implode('-', $nameFix ?? $vehicle['Name']);

        $className = explode('_', $vehicle['ClassName']);
        array_shift($className);

        switch ($vehicle['Name']) {
            case 'Anvil F7C-M Hornet Heartseeker':
                $name = 'F7C-M Super Hornet Heartseeker';
                break;

            case 'Origin 600i':
                $name = '600i Explorer';
                break;

            case 'Crusader A2 Hercules Starlifter':
                $name = 'A2 Hercules';
                break;

            case 'Crusader C2 Hercules Starlifter':
                $name = 'C2 Hercules';
                break;

            case 'Crusader M2 Hercules Starlifter':
                $name = 'M2 Hercules';
                break;

            case 'Crusader Mercury Star Runner':
                $name = 'Mercury';
                break;

            case 'Crusader Ares Star Fighter Ion':
                $name = 'Ares Ion';
                break;

            case 'Crusader Ares Star Fighter Inferno':
                $name = 'Ares Inferno';
                break;

            case 'Drake Dragonfly':
                $name = 'Dragonfly Black';
                break;

            case 'Kruger P-72 Archimedes':
                $name = 'P72 Archimedes';
                break;

            case 'Origin M50 Interceptor':
                $name = 'M50';
                break;

            case 'Origin 85X Limited':
                $name = '85X';
                break;
        }

        return $this->queryForName(['name', $name]) ??
            $this->queryForName(['name', 'LIKE', sprintf('%%%s%%', $name)]) ??
            $this->queryForName(['name', $nameDashed]) ??
            $this->queryForName(['name', 'LIKE', sprintf('%%%s%%', $nameDashed)]);
    }

    /**
     * Just a small query wrapper
     *
     * @param array $config
     * @return Model|null
     */
    private function queryForName(array $config): ?Model
    {
        return \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::query()->where(...$config)->first();
    }

    /**
     * "Rounds" to a given precision
     *
     * @param $data
     * @return float|int
     */
    private function numFormat($data): float|int
    {
        $num = $data ?? 0;

        if ($num === 'NaN' || $num === 'Infinity') {
            return 0;
        }

        $negation = ($num < 0) ? (-1) : 1;
        $coefficient = 10 ** 3;

        return $negation * floor((abs((float)$num) * $coefficient)) / $coefficient;
    }


    private function getItemUUID(string $className): ?string
    {
        return \App\Models\SC\Item\Item::query()
            ->where('class_name', strtolower($className))
            ->first(['uuid'])->uuid ?? null;
    }

    /**
     * Creates all hardpoints found on a vehicle
     * Iterates through all sup-hardpoints also
     *
     * @param \App\Models\SC\Vehicle\Vehicle $vehicle
     * @param array $rawData
     */
    private function createHardpoints(\App\Models\SC\Vehicle\Vehicle $vehicle, array $rawData): void
    {
        $entries = Arr::get(
            $rawData,
            'Entity.Components.SEntityComponentDefaultLoadoutParams.loadout.SItemPortLoadoutManualParams.entries'
        );

        if ($entries === null) {
            return;
        }

        $hardpoints = [];
        $this->mapHardpoints($rawData['Vehicle']['Parts'], $hardpoints);

        collect($entries)
            ->filter(function ($entry) use ($hardpoints) {
                return isset($hardpoints[strtolower($entry['itemPortName'] ?? '')]);
            })
            ->chunk(5)
            ->each(function (Collection $entries) use ($hardpoints, $vehicle) {
                $entries
                    ->each(function ($hardpoint) use ($hardpoints, $vehicle) {
                        $itemUuid = null;
                        if (isset($hardpoint['entityClassName']) && !empty($hardpoint['entityClassName'])) {
                            $itemUuid = $this->getItemUUID($hardpoint['entityClassName']);
                        }

                        $itemPortName = strtolower($hardpoint['itemPortName']);

                        $point = $vehicle->hardpoints()->updateOrCreate([
                            'hardpoint_name' => $hardpoint['itemPortName'],
                        ], [
                            'class_name' => $hardpoint['entityClassName'],
                            'equipped_item_uuid' => $itemUuid,
                            'min_size' => $hardpoints[$itemPortName]['ItemPort']['minsize'] ?? 0,
                            'max_size' => $hardpoints[$itemPortName]['ItemPort']['maxsize'] ?? 0,
                        ]);

                        $this->createSubPoint(
                            Arr::get($hardpoint, 'loadout.SItemPortLoadoutManualParams.entries', []),
                            $point,
                            $vehicle
                        );
                    });
            });
    }

    /**
     * Flat-maps all hardpoints from hardpoint name to hardpoint data
     * This is used to get the min and max sizes later on
     *
     * @param array $parts
     * @param array $out
     */
    private function mapHardpoints(array $parts, array &$out): void
    {
        foreach ($parts as $part) {
            if (!isset($part['name'])) {
                continue;
            }

            if (isset($part['Parts'])) {
                $this->mapHardpoints($part['Parts'], $out);
                unset($part['Parts']);
            }

            if (($part['class'] ?? '') === 'ItemPort') {
                unset($part['ItemPort']['Connections'], $part['ItemPort']['ControllerDef'], $part['ItemPort']['Types']);
                $out[strtolower($part['name'])] = $part;
            }
        }
    }

    /**
     * This runs on each child hardpoint found on a hardpoint recursively
     *
     * @param array $entries
     * @param Hardpoint $parent
     * @param \App\Models\SC\Vehicle\Vehicle $vehicle
     */
    private function createSubPoint(array $entries, Hardpoint $parent, \App\Models\SC\Vehicle\Vehicle $vehicle): void
    {
        foreach ($entries as $subPoint) {
            if (empty($subPoint['entityClassName'])) {
                continue;
            }

            $point = $vehicle->hardpoints()->updateOrCreate([
                'hardpoint_name' => $subPoint['itemPortName'],
                'parent_hardpoint_id' => $parent->id,
            ], [
                    'class_name' => $subPoint['entityClassName'],
                    'equipped_item_uuid' => $this->getItemUUID($subPoint['entityClassName']),
                ]
            );

            $subEntries = Arr::get($subPoint, 'loadout.SItemPortLoadoutManualParams.entries');

            if (!empty($subEntries)) {
                $this->createSubPoint($subEntries, $point, $vehicle);
            }
        }
    }
}
