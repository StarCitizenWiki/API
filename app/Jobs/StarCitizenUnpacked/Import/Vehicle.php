<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use App\Models\StarCitizenUnpacked\VehicleHardpoint;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    /**
     * These are item types that added as an item when found as loadout on a hardpoint
     *
     * @var array|string[]
     */
    private array $createItemTypes = [
        'FuelTank',
        'QuantumFuelTank',
        'FuelIntake',
        'Turret',
        'TurretBase',
        'MiningArm',
        'MainThruster',
        'ManneuverThruster',
        'SelfDestruct',
        'ToolArm',
        'WeaponDefensive',
        'WeaponMining',
        'WeaponMount',
        'Radar',
        'Cargo',
        'CargoGrid',
    ];

    /**
     * Parses a json file to array
     *
     * @var \App\Services\Parser\StarCitizenUnpacked\ShipItems\ShipItem
     */
    private \App\Services\Parser\StarCitizenUnpacked\ShipItems\ShipItem $parser;

    /**
     * Creates a model from parser
     *
     * @var ShipItems
     */
    private ShipItems $creator;

    public function __construct()
    {
        $this->parser = new \App\Services\Parser\StarCitizenUnpacked\ShipItems\ShipItem();
        $this->creator = new ShipItems();
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

        // TODO: Yeah this needs to go
        VehicleHardpoint::query()->truncate();

        collect($vehicles)->chunk(5)->each(function (Collection $chunk) {
            $chunk
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'fw22nfz') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'modifiers') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'SM_TE') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'Bombless') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'BIS29') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'Indestructible') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'Prison') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'NoCrimesAgainst') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'Unmanned') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'F7A_Mk1') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'CINEMATIC_ONLY') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'NO_CUSTOM') === false;
                })
                ->filter(function (array $vehicle) {
                    return stripos($vehicle['ClassName'], 'Test') === false;
                })
                ->filter(function (array $vehicle) {
                    return $vehicle['ClassName'] !== 'TEST_Boat';
                })
                ->map(function (array $vehicle) {
                    try {
                        $vehicle['rawData'] = json_decode(File::get(storage_path(sprintf(
                            'app/api/scunpacked-data/v2/ships/%s-raw.json',
                            strtolower($vehicle['ClassName'])
                        ))), true, 512, JSON_THROW_ON_ERROR);
                    } catch (FileNotFoundException | JsonException $e) {
                    }

                    return $vehicle;
                })->each(function (array $vehicle) {
                    if (!isset($vehicle['rawData']['Entity']['__ref'])) {
                        return;
                    }

                    try {
                        /** @var \App\Models\StarCitizenUnpacked\Vehicle $vehicleModel */
                        $vehicleModel = \App\Models\StarCitizenUnpacked\Vehicle::updateOrCreate([
                            'class_name' => $vehicle['ClassName']
                        ], $this->getVehicleModelArray($vehicle));

                        $vehicleModel->refresh();

                        if ($vehicleModel->hardpoints->count() === 0) {
                            $this->createHardpoints($vehicleModel, $vehicle['rawData']);
                        }
                    } catch (Exception $e) {
                        app('Log')::warning($e->getMessage());
                    }
                });
        });
    }

    public function getVehicleModelArray(array $vehicle): array
    {
        return [
            'uuid' => $vehicle['rawData']['Entity']['__ref'],

            'shipmatrix_id' => $this->tryGetShipmatrixIdForVehicle($vehicle)->id ?? 0,
            'name' => $vehicle['Name'],
            'career' => $vehicle['Career'],
            'role' => $vehicle['Role'],
            'is_ship' => (bool)$vehicle['IsSpaceship'],
            'size' => $vehicle['Size'],
            'width' => $vehicle['Width'],
            'height' => $vehicle['Height'],
            'length' => $vehicle['Length'],
            'cargo_capacity' => $vehicle['Cargo'],
            'crew' => $vehicle['Crew'],
            'weapon_crew' => $vehicle['WeaponCrew'],
            'operations_crew' => $vehicle['OperationsCrew'],
            'mass' => $vehicle['Mass'],

            'health' => $this->numFormat($this->calculateHealth($vehicle['rawData']['Vehicle']['Parts'])),

            'scm_speed' => $this->numFormat($vehicle['FlightCharacteristics']['ScmSpeed']),
            'max_speed' => $this->numFormat($vehicle['FlightCharacteristics']['MaxSpeed']),

            'zero_to_scm' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToScm']),
            'zero_to_max' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToMax']),

            'scm_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['ScmToZero']),
            'max_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['MaxToZero']),

            'pitch' => $this->numFormat($vehicle['FlightCharacteristics']['Pitch']),
            'yaw' => $this->numFormat($vehicle['FlightCharacteristics']['Yaw']),
            'roll' => $this->numFormat($vehicle['FlightCharacteristics']['Roll']),

            'acceleration_main' => $this->numFormat($vehicle['FlightCharacteristics']['Acceleration']['Main'] ?? 0),
            'acceleration_retro' => $this->numFormat($vehicle['FlightCharacteristics']['Acceleration']['Retro'] ?? 0),
            'acceleration_vtol' => $this->numFormat($vehicle['FlightCharacteristics']['Acceleration']['Vtol'] ?? 0),
            'acceleration_maneuvering' => $this->numFormat($vehicle['FlightCharacteristics']['Acceleration']['Maneuvering'] ?? 0),

            'acceleration_g_main' => $this->numFormat($vehicle['FlightCharacteristics']['AccelerationG']['Main'] ?? 0),
            'acceleration_g_retro' => $this->numFormat($vehicle['FlightCharacteristics']['AccelerationG']['Retro'] ?? 0),
            'acceleration_g_vtol' => $this->numFormat($vehicle['FlightCharacteristics']['AccelerationG']['Vtol'] ?? 0),
            'acceleration_g_maneuvering' => $this->numFormat($vehicle['FlightCharacteristics']['AccelerationG']['Maneuvering'] ?? 0),

            'fuel_capacity' => $this->numFormat($vehicle['Propulsion']['FuelCapacity'] ?? 0),
            'fuel_intake_rate' => $this->numFormat($vehicle['Propulsion']['FuelIntakeRate'] ?? 0),

            'fuel_usage_main' => $this->numFormat($vehicle['Propulsion']['FuelUsage']['Main'] ?? 0),
            'fuel_usage_retro' => $this->numFormat($vehicle['Propulsion']['FuelUsage']['Retro'] ?? 0),
            'fuel_usage_vtol' => $this->numFormat($vehicle['Propulsion']['FuelUsage']['Vtol'] ?? 0),
            'fuel_usage_maneuvering' => $this->numFormat($vehicle['Propulsion']['FuelUsage']['Maneuvering'] ?? 0),

            'quantum_speed' => $this->numFormat($vehicle['QuantumTravel']['Speed'] ?? 0),
            'quantum_spool_time' => $this->numFormat($vehicle['QuantumTravel']['SpoolTime'] ?? 0),
            'quantum_fuel_capacity' => $this->numFormat($vehicle['QuantumTravel']['FuelCapacity'] ?? 0),
            'quantum_range' => $this->numFormat($vehicle['QuantumTravel']['Range'] ?? 0),

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
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
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
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private function queryForName(array $config)
    {
        return \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::query()->where(...$config)->first();
    }

    /**
     * "Rounds" to a given precision
     *
     * @param $data
     * @return float|int
     */
    private function numFormat($data)
    {
        $num = $data ?? 0;

        if ($num === 'NaN' || $num === 'Infinity') {
            return 0;
        }

        $negation = ($num < 0) ? (-1) : 1;
        $coefficient = 10 ** 3;

        return $negation * floor((abs((float)$num) * $coefficient)) / $coefficient;
    }

    /**
     * Creates a ShipItem model for a given className
     * Loads the item definition from file and parses it
     *
     * @param string $className
     * @return string|null
     */
    private function createModel(string $className): ?string
    {
        try {
            $item = File::get(
                storage_path(
                    sprintf(
                        'app/api/scunpacked-data/v2/items/%s-raw.json',
                        str_replace('-', '_', strtolower($className))
                    )
                )
            );

            $itemRaw = json_decode($item, true, 512, JSON_THROW_ON_ERROR);

            if (isset($itemRaw['__ref'])) {
                $item = ShipItem::query()->firstWhere('uuid', $itemRaw['__ref']);

                // phpcs:ignore
                if (in_array(($itemRaw['Components']['SAttachableComponentParams']['AttachDef']['Type'] ?? ''), $this->createItemTypes, true)) {
                    // phpcs:ignore
                    $itemRaw['Classification'] = 'Ship.' . $itemRaw['Components']['SAttachableComponentParams']['AttachDef']['Type'];
                    $this->parser->setItems(collect([$itemRaw]));

                    $data = $this->parser->getData()->first();

                    if ($data !== null) {
                        $this->creator->createModel($data);
                    }

                    $itemUuid = $itemRaw['__ref'];
                } elseif ($item !== null) {
                    $itemUuid = $item->uuid;
                }
            }
        } catch (FileNotFoundException | JsonException $e) {
        }

        return $itemUuid ?? null;
    }

    /**
     * Creates all hardpoints found on a vehicle
     * Iterates through all sup-hardpoints also
     *
     * @param \App\Models\StarCitizenUnpacked\Vehicle $vehicle
     * @param array $rawData
     */
    private function createHardpoints(\App\Models\StarCitizenUnpacked\Vehicle $vehicle, array $rawData): void
    {
        // phpcs:ignore
        if (!isset($rawData['Entity']['Components']['SEntityComponentDefaultLoadoutParams']['loadout']['SItemPortLoadoutManualParams']['entries'])) {
            return;
        }

        $hardpoints = [];
        $this->mapHardpoints($rawData['Vehicle']['Parts'], $hardpoints);

        // phpcs:ignore
        collect($rawData['Entity']['Components']['SEntityComponentDefaultLoadoutParams']['loadout']['SItemPortLoadoutManualParams']['entries'])
            ->filter(function ($entry) use ($hardpoints) {
                return isset($hardpoints[strtolower($entry['itemPortName'] ?? '')]);
            })
            ->chunk(5)
            ->each(function (Collection $entries) use ($hardpoints, $vehicle) {
                $entries
                    ->each(function ($hardpoint) use ($hardpoints, $vehicle) {
                        $itemUuid = null;
                        if (isset($hardpoint['entityClassName']) && !empty($hardpoint['entityClassName'])) {
                            $itemUuid = $this->createModel($hardpoint['entityClassName']);
                        }

                        $itemPortName = strtolower($hardpoint['itemPortName']);

                        $point = $vehicle->hardpoints()->create(
                            [
                                'hardpoint_name' => $hardpoint['itemPortName'],
                                'class_name' => $hardpoint['entityClassName'],
                                'equipped_vehicle_item_uuid' => $itemUuid,
                                'min_size' => $hardpoints[$itemPortName]['ItemPort']['minsize'] ?? 0,
                                'max_size' => $hardpoints[$itemPortName]['ItemPort']['maxsize'] ?? 0,
                            ]
                        );

                        // "Fix" for the Cutlass Steel mounted weapons
                        if (strpos($hardpoint['entityClassName'], 'WeaponMount_Gun_S1_DRAK_Cutlass_Steel') !== false) {
                            $hardpoint['loadout'] = [
                                'SItemPortLoadoutManualParams' => [
                                    'entries' => [
                                        [
                                        'itemPortName' => 'weapon',
                                        'entityClassName' => 'GATS_BallisticGatling_Mounted_S1',
                                        'loadout' => []
                                        ]
                                    ],
                                ]
                            ];
                        }

                        // phpcs:disable
                        if (isset($hardpoint['loadout']['SItemPortLoadoutManualParams']['entries']) &&
                            !empty($hardpoint['loadout']['SItemPortLoadoutManualParams']['entries'])) {
                            $this->createSubPoint(
                                $hardpoint['loadout']['SItemPortLoadoutManualParams']['entries'],
                                $point,
                                $vehicle
                            );
                        }
                        // phpcs:enable
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
     * @param VehicleHardpoint $parent
     * @param \App\Models\StarCitizenUnpacked\Vehicle $vehicle
     */
    private function createSubPoint(array $entries, VehicleHardpoint $parent, \App\Models\StarCitizenUnpacked\Vehicle $vehicle): void
    {
        foreach ($entries as $subPoint) {
            if (empty($subPoint['entityClassName'])) {
                continue;
            }

            try {
                $item = File::get(
                    storage_path(
                        sprintf(
                            'app/api/scunpacked-data/v2/items/%s-raw.json',
                            str_replace('-', '_', strtolower($subPoint['entityClassName']))
                        )
                    )
                );

                $item = json_decode($item, true, 512, JSON_THROW_ON_ERROR);

                if (isset($subPoint['entityClassName'])) {
                    $this->createModel($subPoint['entityClassName']);
                }

                $point = $vehicle->hardpoints()->create(
                    [
                        'hardpoint_name' => $subPoint['itemPortName'],
                        'class_name' => $subPoint['entityClassName'],
                        'parent_hardpoint_id' => $parent->id,
                        'equipped_vehicle_item_uuid' => $item['__ref'],
                    ]
                );

                // phpcs:disable
                if (isset($subPoint['loadout']['SItemPortLoadoutManualParams']['entries']) &&
                    !empty($subPoint['loadout']['SItemPortLoadoutManualParams']['entries'])) {
                    $this->createSubPoint(
                        $subPoint['loadout']['SItemPortLoadoutManualParams']['entries'],
                        $point,
                        $vehicle
                    );
                }
                // phpcs:enable
            } catch (JsonException | FileNotFoundException $e) {
                continue;
            }
        }
    }

    /**
     * Recursively sums all damageMax params from all parts
     *
     * @param array $parts
     * @return float
     */
    private function calculateHealth(array $parts): float
    {
        $health = 0;

        foreach ($parts as $part) {
            if (isset($part['damagemax']) || isset($part['damageMax'])) {
                $health += ($part['damagemax'] ?? 0) + ($part['damageMax'] ?? 0);
            }

            if (isset($part['Parts'])) {
                $health += $this->calculateHealth($part['Parts']);
            }
        }

        return $health;
    }
}
