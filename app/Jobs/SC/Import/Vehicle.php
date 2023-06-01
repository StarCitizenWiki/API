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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;

class Vehicle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Collection $hardpoints;

    private array $shipData;

    public function __construct(array $shipData)
    {
        $this->shipData = $shipData;
        $this->hardpoints = new Collection();
    }

    public function handle(): void
    {
        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        $vehicle = $this->shipData;

        try {
            $rawData = File::get(storage_path(sprintf('app/%s', $vehicle['filePathV2'])));

            $vehicle['rawData'] = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e->getMessage());
        }

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
        } else {
            $vehicleModel->item->update([
                'version' => config('api.sc_data_version'),
            ]);
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

        $vehicleModel->hardpoints()->whereNotIn('hardpoint_name', $this->hardpoints)->delete();
        $this->createHandlingModel($vehicleModel, $vehicle['rawData']);
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
            'width' => $vehicle['Width'] ?? 0,
            'height' => $vehicle['Height'] ?? 0,
            'length' => $vehicle['Length'] ?? 0,

            'crew' => $vehicle['Crew'],
            'weapon_crew' => $vehicle['WeaponCrew'],
            'operations_crew' => $vehicle['OperationsCrew'],
            'mass' => $vehicle['Mass'],
            'health' => $vehicle['Health'] ?? null,

            'zero_to_scm' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToScm']),
            'zero_to_max' => $this->numFormat($vehicle['FlightCharacteristics']['ZeroToMax']),

            'scm_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['ScmToZero']),
            'max_to_zero' => $this->numFormat($vehicle['FlightCharacteristics']['MaxToZero']),

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
            case 'Anvil C8R Pisces Rescue':
                $name = 'C8R Pisces';
                break;

            case 'Anvil F7C-M Hornet Heartseeker':
                $name = 'F7C-M Super Hornet Heartseeker';
                break;

            case 'Origin 600i':
                $name = '600i Explorer';
                break;

            case 'C.O. Mustang CitizenCon 2948 Edition':
                $name = 'Mustang Alpha Vindicator';
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
                $name = 'P-72 Archimedes';
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
        $this->mapHardpoints(Arr::get($rawData, 'Vehicle.Parts', []), $hardpoints);

        collect($entries)
            ->chunk(5)
            ->each(function (Collection $entries) use ($hardpoints, $vehicle) {
                $entries
                    ->each(function ($hardpoint) use ($hardpoints, $vehicle) {
                        $itemUuid = null;
                        if (!empty($hardpoint['entityClassName'])) {
                            $itemUuid = $this->getItemUUID($hardpoint['entityClassName']);
                        }

                        $itemPortName = strtolower($hardpoint['itemPortName']);
                        $this->hardpoints->push($hardpoint['itemPortName']);

                        $point = $vehicle->hardpoints()->updateOrCreate([
                            'hardpoint_name' => $hardpoint['itemPortName'],
                        ], [
                            'class_name' => $hardpoint['entityClassName'],
                            'equipped_item_uuid' => $itemUuid,
                            'min_size' => $hardpoints[$itemPortName]['ItemPort']['minsize'] ?? null,
                            'max_size' => $hardpoints[$itemPortName]['ItemPort']['maxsize'] ?? null,
                        ]);

                        $this->createSubPoint(
                            Arr::get($hardpoint, 'loadout.SItemPortLoadoutManualParams.entries', []),
                            $point,
                            $vehicle
                        );
                    });
            });

        // Add Hardpoints only found on the Vehicle.Parts key
        collect($hardpoints)
            // Create vehicle parts
            ->each(function ($hardpoint) use ($vehicle) {
                if (!empty($hardpoint['name']) && isset($hardpoint['damageMax']) && $hardpoint['damageMax'] > 0) {
                    $vehicle->parts()->updateOrCreate([
                        'name' => $hardpoint['name'],
                    ], [
                        'parent' => $hardpoint['parent'] ?? null,
                        'damage_max' => $hardpoint['damageMax'],
                    ]);
                }
            })
            ->whereNotIn('name', $this->hardpoints)
            ->filter(function (array $hardpoint) {
                // Filter out some
                return !Str::contains($hardpoint['name'], [
                    '$slot',
                    '_trail_',
                    '_SQUIB_',
                    'audio',
                    'animated',
                    'Helper',
                    'LandingGear',
                    'gameplay',
                    'ObjectContainer',
                ], true);
            })
            ->each(function ($hardpoint) use ($vehicle) {
                $where = [
                    'hardpoint_name' => $hardpoint['name'],
                ];

                if (str_starts_with($hardpoint['parent'] ?? '', 'hardpoint')) {
                    $parent = $vehicle->hardpoints()->where('hardpoint_name', $hardpoint['parent'])->first()?->id;
                    $where['parent_hardpoint_id'] = $parent;
                }

                $vehicle->hardpoints()->updateOrCreate($where, [
                    'min_size' => Arr::get($hardpoint, 'ItemPort.minsize'),
                    'max_size' => Arr::get($hardpoint, 'ItemPort.maxsize'),
                ]);

                $this->hardpoints->push($hardpoint['name']);
            });
    }

    /**
     * Flat-maps all hardpoints from hardpoint name to hardpoint data
     * This is used to get the min and max sizes later on
     *
     * @param array $parts
     * @param array $out
     * @param string|null $parent
     */
    private function mapHardpoints(array $parts, array &$out, ?string $parent = null): void
    {
        foreach ($parts as $part) {
            if (!isset($part['name'])) {
                continue;
            }

            if ($parent !== null) {
                $part['parent'] = $parent;
            }

            if (isset($part['Parts'])) {
                $this->mapHardpoints($part['Parts'], $out, $part['name']);
                unset($part['Parts']);
            }

            unset($part['ItemPort']['Connections'], $part['ItemPort']['ControllerDef'], $part['ItemPort']['Types']);
            $out[strtolower($part['name'])] = $part;
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

            $this->hardpoints->push($subPoint['itemPortName']);
            $point = $vehicle->hardpoints()->updateOrCreate([
                'hardpoint_name' => $subPoint['itemPortName'],
                'parent_hardpoint_id' => $parent->id,
            ], [
                'class_name' => $subPoint['entityClassName'],
                'equipped_item_uuid' => $this->getItemUUID($subPoint['entityClassName']),
            ]);

            $subEntries = Arr::get($subPoint, 'loadout.SItemPortLoadoutManualParams.entries');

            if (!empty($subEntries)) {
                $this->createSubPoint($subEntries, $point, $vehicle);
            }
        }
    }

    private function createHandlingModel(\App\Models\SC\Vehicle\Vehicle $vehicle, array $rawData): void
    {
        $handlingData = Arr::get($rawData, 'Vehicle.MovementParams.ArcadeWheeled');
        if ($handlingData === null) {
            return;
        }

        $vehicle->handling()->updateOrCreate([
            'max_speed' => Arr::get($handlingData, 'Handling.Power.topSpeed'),
            'reverse_speed' => Arr::get($handlingData, 'Handling.Power.reverseSpeed'),
            'acceleration' => Arr::get($handlingData, 'Handling.Power.acceleration'),
            'deceleration' => Arr::get($handlingData, 'Handling.Power.decceleration'),
            'v0_steer_max' => Arr::get($handlingData, 'v0SteerMax'),
            'kv_steer_max' => Arr::get($handlingData, 'kvSteerMax'),
            'vmax_steer_max' => Arr::get($handlingData, 'vMaxSteerMax'),
        ]);
    }
}
