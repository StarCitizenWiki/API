<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_heat_data_v2',
    title: 'Item Heat Data',
    properties: [
        new OA\Property(property: 'temperature_to_ir', type: 'double', nullable: true),
        new OA\Property(property: 'ir_temperature_threshold', type: 'double', nullable: true),
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
        new OA\Property(property: 'ir_emission', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ItemHeatDataResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'temperature_to_ir' => $this->temperature_to_ir,
            'ir_temperature_threshold' => $this->ir_temperature_threshold,
            'overpower_heat' => $this->overpower_heat,
            'overclock_threshold_min' => $this->overclock_threshold_min,
            'overclock_threshold_max' => $this->overclock_threshold_max,
            'thermal_energy_base' => $this->thermal_energy_base,
            'thermal_energy_draw' => $this->thermal_energy_draw,
            'thermal_conductivity' => $this->thermal_conductivity,
            'specific_heat_capacity' => $this->specific_heat_capacity,
            'mass' => $this->mass,
            'surface_area' => $this->surface_area,
            'start_cooling_temperature' => $this->start_cooling_temperature,
            'max_cooling_rate' => $this->max_cooling_rate,
            'max_temperature' => $this->max_temperature,
            'min_temperature' => $this->min_temperature,
            'overheat_temperature' => $this->overheat_temperature,
            'recovery_temperature' => $this->recovery_temperature,
            'misfire_min_temperature' => $this->misfire_min_temperature,
            'misfire_max_temperature' => $this->misfire_max_temperature,
            'ir_emission' => $this->infrared_emission,
        ];
    }
}
