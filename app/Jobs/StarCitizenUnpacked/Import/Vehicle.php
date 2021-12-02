<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Hardpoint;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
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

class Vehicle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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

        collect($vehicles)->chunk(10)->each(function (Collection $chunk) {
            $chunk->map(function (array $vehicle) {
                try {
                    $vehicle['rawData'] = json_decode(File::get(storage_path(sprintf(
                        'app/api/scunpacked-data/v2/ships/%s-raw.json',
                        strtolower($vehicle['ClassName'])
                    ))), true, 512, JSON_THROW_ON_ERROR);
                } catch (FileNotFoundException | JsonException $e) {
                    //
                }

                return $vehicle;
            })->each(function (array $vehicle) {
                if (!isset($vehicle['rawData']['Entity']['__ref'])) {
                    return;
                }

                try {
                    $vehicleModel = \App\Models\StarCitizenUnpacked\Vehicle::updateOrCreate([
                        'class_name' => $vehicle['ClassName']
                    ], $this->getVehicleModelArray($vehicle));

                    $this->createHardpoints($vehicleModel, $vehicle['rawData']);
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

            'shipmatrix_id' => $this->tryGetShipmatrixIdForVehicle($vehicle)->id ?? -1,
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

            'health_body' => $this->numFormat(
                $vehicle['DamageBeforeDestruction']['Body'] ??
                $vehicle['DamageBeforeDestruction']['rollcage_main'] ??
                0
            ),

            'scm_speed' => $this->numFormat($vehicle['FlightCharacteristics']['ScmSpeed']),
            'max_speed' => $this->numFormat($vehicle['FlightCharacteristics']['MaxSpeed']),

            'zero_to_scm' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToScm']),
            'zero_to_max' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToMax']),

            'scm_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['ScmToZero']),
            'max_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['MaxToZero']),

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

    private function tryGetShipmatrixIdForVehicle(array $vehicle)
    {
        $nameFix = explode(' ', $vehicle['Name']);
        array_shift($nameFix);
        $name = implode(' ', $nameFix ?? $vehicle['Name']);
        $nameDashed = implode('-', $nameFix ?? $vehicle['Name']);

        $className = explode('_', $vehicle['ClassName']);
        array_shift($className);

        switch ($vehicle['Name']) {
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

            case 'Drake Dragonfly':
                $name = 'Dragonfly Black';
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

    private function queryForName(array $config)
    {
        return \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::query()->where(...$config)->first();
    }

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

    private function createHardpoints(\App\Models\StarCitizenUnpacked\Vehicle $vehicle, array $rawData): void
    {
        if (!isset($rawData['Entity']['Components']['SEntityComponentDefaultLoadoutParams']['loadout']['SItemPortLoadoutManualParams']['entries'])) {
            return;
        }

        $hardpoints = [];
        $this->mapHardpoints($rawData['Vehicle']['Parts'], $hardpoints);

        $toSync = [];

        $parser = new \App\Services\Parser\StarCitizenUnpacked\ShipItems\ShipItem();

        collect($rawData['Entity']['Components']['SEntityComponentDefaultLoadoutParams']['loadout']['SItemPortLoadoutManualParams']['entries'])
            ->filter(function ($entry) use ($hardpoints) {
                return isset($hardpoints[$entry['itemPortName'] ?? '']);
            })
            ->chunk(10)
            ->each(function (Collection $entries) use ($hardpoints, &$toSync, $vehicle, $parser) {
                try {
                    $entries
                        ->each(function ($hardpoint) use ($hardpoints, &$toSync, $vehicle, $parser) {
                            $point = Hardpoint::query()->firstOrCreate(['name' => $hardpoint['itemPortName']]);

                            $itemUuid = null;
                            if (isset($hardpoint['entityClassName']) && !empty($hardpoint['entityClassName'])) {
                                try {
                                    $item = File::get(
                                        storage_path(
                                            sprintf(
                                                'app/api/scunpacked-data/v2/items/%s-raw.json',
                                                str_replace('-', '_', strtolower($hardpoint['entityClassName']))
                                            )
                                        )
                                    );

                                    $itemRaw = json_decode($item, true, 512, JSON_THROW_ON_ERROR);


                                    if (isset($itemRaw['__ref'])) {
                                        $item = ShipItem::query()->firstWhere('uuid', $itemRaw['__ref']);

                                        if (($itemRaw['Components']['SAttachableComponentParams']['AttachDef']['Type'] ?? '') === 'Turret' && !Str::contains($itemRaw['ClassName'] ?? 'Remote', ['Remote', 'AI_Turret', 'Item_Turret'])) {
                                            $itemRaw['Classification'] = 'Ship.Turret';
                                            $parser->setItems(collect([$itemRaw]));
                                            $creator = new ShipItems();
                                            $data = $parser->getData()->first();
                                            if ($data !== null) {
                                                $creator->createModel($data);
                                            }

                                            $itemUuid = $itemRaw['__ref'];
                                        } elseif ($item !== null) {
                                            $itemUuid = $item->uuid;
                                        }
                                    }
                                } catch (FileNotFoundException | JsonException $e) {
                                    //
                                }
                            }

                            if ($point->id !== null) {
                                $toSync[$point->id] = [
                                    'equipped_vehicle_item_uuid' => $itemUuid,
                                    'min_size' => $hardpoints[$hardpoint['itemPortName']]['ItemPort']['minsize'] ?? 0,
                                    'max_size' => $hardpoints[$hardpoint['itemPortName']]['ItemPort']['maxsize'] ?? 0,
                                    'vehicle_id' => $vehicle->id,
                                ];
                            }

                            if (isset($hardpoint['loadout']['SItemPortLoadoutManualParams']['entries']) && !empty($hardpoint['loadout']['SItemPortLoadoutManualParams']['entries'])) {
                                foreach ($hardpoint['loadout']['SItemPortLoadoutManualParams']['entries'] as $subPoint) {
                                    $subPointModel = Hardpoint::query()->firstOrCreate(['name' => $subPoint['itemPortName']]);

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

                                        $toSync[$subPointModel->id] = [
                                            'parent_hardpoint_id' => $point->id,
                                            'equipped_vehicle_item_uuid' => $item['__ref'],
                                            'vehicle_id' => $vehicle->id,
                                        ];
                                    } catch (JsonException | FileNotFoundException $e) {
                                        continue;
                                    }
                                }
                            }
                        });
                } catch (Exception $e) {
                }
            });

        $vehicle->hardpoints()->sync($toSync);
    }

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
                $out[$part['name']] = $part;
            }
        }
    }
}
