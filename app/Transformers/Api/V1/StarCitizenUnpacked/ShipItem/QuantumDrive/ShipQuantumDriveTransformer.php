<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\QuantumDrive;

use App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDrive;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;
use League\Fractal\Resource\Collection;

class ShipQuantumDriveTransformer extends AbstractCommodityTransformer
{
    protected $defaultIncludes = [
        'modes'
    ];

    public function transform(QuantumDrive $item): array
    {
        return [
            'quantum_fuel_requirement' => $item->quantum_fuel_requirement,
            'jump_range' => $item->jump_range,
            'disconnect_range' => $item->disconnect_range,
            'thermal_energy_draw' => [
                'pre_ramp_up' => $item->pre_ramp_up_thermal_energy_draw,
                'ramp_up' => $item->ramp_up_thermal_energy_draw,
                'in_flight' => $item->in_flight_thermal_energy_draw,
                'ramp_down' => $item->ramp_down_thermal_energy_draw,
                'post_ramp_down' => $item->post_ramp_down_thermal_energy_draw,
            ],
        ];
    }

    public function includeModes(QuantumDrive $drive): Collection
    {
        return $this->collection($drive->modes, new ShipQuantumDriveModeTransformer());
    }
}
