<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\QuantumDrive;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class QuantumDriveResource extends AbstractBaseResource
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
            'quantum_fuel_requirement' => $this->quantum_fuel_requirement,
            'jump_range' => $this->jump_range,
            'disconnect_range' => $this->disconnect_range,
            'thermal_energy_draw' => [
                'pre_ramp_up' => $this->pre_ramp_up_thermal_energy_draw,
                'ramp_up' => $this->ramp_up_thermal_energy_draw,
                'in_flight' => $this->in_flight_thermal_energy_draw,
                'ramp_down' => $this->ramp_down_thermal_energy_draw,
                'post_ramp_down' => $this->post_ramp_down_thermal_energy_draw,
            ],
            'modes' => QuantumDriveModeResource::collection($this->whenLoaded('modes')),
        ];
    }
}
