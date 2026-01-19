<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_heat_connection',
    title: 'Item Heat Connection',
    description: 'Heat connection information, generated from EntityComponentHeatConnection attributes.',
    properties: [
        new OA\Property(property: 'temperature_to_ir', type: 'double', nullable: true),
        new OA\Property(property: 'ir_temperature_threshold', description: 'StartIRTemperature', type: 'double', nullable: true),
        new OA\Property(property: 'overpower_heat', type: 'double', nullable: true),
        new OA\Property(property: 'overclock_threshold_min', type: 'double', nullable: true),
        new OA\Property(property: 'overclock_threshold_max', type: 'double', nullable: true),
        new OA\Property(property: 'thermal_energy_base', type: 'double', nullable: true),
        new OA\Property(property: 'thermal_energy_draw', type: 'double', nullable: true),
        new OA\Property(property: 'thermal_conductivity', type: 'double', nullable: true),
        new OA\Property(property: 'specific_heat_capacity', type: 'double', nullable: true),
        new OA\Property(property: 'mass', type: 'double', nullable: true),
        new OA\Property(property: 'surface_area', type: 'double', nullable: true),
        new OA\Property(property: 'start_cooling_temperature', type: 'double', nullable: true),
        new OA\Property(property: 'max_cooling_rate', type: 'double', nullable: true),
        new OA\Property(property: 'max_temperature', type: 'double', nullable: true),
        new OA\Property(property: 'min_temperature', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_temperature', type: 'double', nullable: true),
        new OA\Property(property: 'recovery_temperature', type: 'double', nullable: true),
        new OA\Property(property: 'misfire_min_temperature', type: 'double', nullable: true),
        new OA\Property(property: 'misfire_max_temperature', type: 'double', nullable: true),
        new OA\Property(property: 'ir_emission', description: '(StartCoolingTemperature - StartIRTemperature) * TemperatureToIR', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ItemHeatConnectionResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'temperature_to_ir' => Arr::get($this, 'TemperatureToIR'),
            'ir_temperature_threshold' => Arr::get($this, 'StartIRTemperature'),
            'overpower_heat' => Arr::get($this, 'OverpowerHeat'),
            'overclock_threshold_min' => Arr::get($this, 'OverclockThresholdMinHeat'),
            'overclock_threshold_max' => Arr::get($this, 'OverclockThresholdMaxHeat'),
            'overclock_threshold_min_heat' => Arr::get($this, 'OverclockThresholdMinHeat'),
            'overclock_threshold_max_heat' => Arr::get($this, 'OverclockThresholdMaxHeat'),
            'thermal_energy_base' => Arr::get($this, 'ThermalEnergyBase'),
            'thermal_energy_draw' => Arr::get($this, 'ThermalEnergyDraw'),
            'thermal_conductivity' => Arr::get($this, 'ThermalConductivity'),
            'specific_heat_capacity' => Arr::get($this, 'SpecificHeatCapacity'),
            'mass' => Arr::get($this, 'Mass'),
            'surface_area' => Arr::get($this, 'SurfaceArea'),
            'start_cooling_temperature' => Arr::get($this, 'StartCoolingTemperature'),
            'max_cooling_rate' => Arr::get($this, 'MaxCoolingRate'),
            'max_temperature' => Arr::get($this, 'MaxTemperature'),
            'min_temperature' => Arr::get($this, 'MinTemperature'),
            'overheat_temperature' => Arr::get($this, 'OverheatTemperature'),
            'recovery_temperature' => Arr::get($this, 'RecoveryTemperature'),
            'misfire_min_temperature' => Arr::get($this, 'MisfireMinTemperature'),
            'misfire_max_temperature' => Arr::get($this, 'MisfireMaxTemperature'),
            'ir_emission' => max(
                0,
                (
                    Arr::get($this, 'StartCoolingTemperature', 0) -
                    Arr::get($this, 'StartIRTemperature', 0)
                ) * Arr::get($this, 'TemperatureToIR', 0)
            ),
        ];
    }
}
