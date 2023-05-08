<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

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
        ];
    }
}
