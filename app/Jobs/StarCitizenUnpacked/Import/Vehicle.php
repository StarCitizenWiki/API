<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
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
            $vehicles = File::get(storage_path(sprintf('app/api/scunpacked-data/v2/ships.json')));
        } catch (FileNotFoundException $e) {
            $this->fail('ship.json not found. Did you clone scunpacked?');
            return;
        }

        try {
            $vehicles = json_decode($vehicles, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail($e->getMessage());

            return;
        }

        collect($vehicles)->map(function (array $vehicle) {
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
                \App\Models\StarCitizenUnpacked\Vehicle::updateOrCreate([
                    'class_name' => $vehicle['ClassName']
                ], $this->getVehicleModelArray($vehicle));
            } catch (Exception $e) {
                app('Log')::warning($e->getMessage());
            }
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
            'is_ship' => $vehicle['IsSpaceship'],
            'size' => $vehicle['Size'],
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

        if ($name === 'M50 Interceptor') {
            $name = 'M50';
        }

        if ($name === '85X Limited') {
            $name = '85X';
        }

        $byName = \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::query()
            ->where('name', 'LIKE', sprintf('%%%s%%', $name))
            ->first();

        $byDashedName = \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::query()
            ->where('name', 'LIKE', sprintf('%%%s%%', $nameDashed))
            ->first();

        return $byName ?? $byDashedName;
    }

    private function numFormat($data)
    {
        $num = $data ?? 0;

        if ($num === 'NaN' || $num === 'Infinity') {
            return 0;
        }

        return $num;
    }
}
