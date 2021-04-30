<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItemHeatData;
use League\Fractal\TransformerAbstract;

class ShipItemHeatDataTransformer extends TransformerAbstract
{
    public function transform(ShipItemHeatData $item): array
    {
        return array_filter([
            'temperature_to_ir' => $item->temperature_to_ir,
            'overpower_heat' => $item->overpower_heat,
            'overclock_threshold_min' => $item->overclock_threshold_min,
            'overclock_threshold_max' => $item->overclock_threshold_max,
            'thermal_energy_base' => $item->thermal_energy_base,
            'thermal_energy_draw' => $item->thermal_energy_draw,
            'thermal_conductivity' => $item->thermal_conductivity,
            'specific_heat_capacity' => $item->specific_heat_capacity,
            'mass' => $item->mass,
            'surface_area' => $item->surface_area,
            'start_cooling_temperature' => $item->start_cooling_temperature,
            'max_cooling_rate' => $item->max_cooling_rate,
            'max_temperature' => $item->max_temperature,
            'min_temperature' => $item->min_temperature,
            'overheat_temperature' => $item->overheat_temperature,
            'recovery_temperature' => $item->recovery_temperature,
            'misfire_min_temperature' => $item->misfire_min_temperature,
            'misfire_max_temperature' => $item->misfire_max_temperature,
        ]);
    }
}
