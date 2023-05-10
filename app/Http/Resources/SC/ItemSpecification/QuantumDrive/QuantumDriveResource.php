<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\QuantumDrive;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'quantum_drive_v2',
    title: 'Quantum Drive',
    properties: [
        new OA\Property(property: 'quantum_fuel_requirement', type: 'double', nullable: true),
        new OA\Property(property: 'jump_range', type: 'double', nullable: true),
        new OA\Property(property: 'disconnect_range', type: 'double', nullable: true),
        new OA\Property(property: 'thermal_energy_draw', properties: [
            new OA\Property(property: 'pre_ramp_up', type: 'double', nullable: true),
            new OA\Property(property: 'ramp_up', type: 'double', nullable: true),
            new OA\Property(property: 'in_flight', type: 'double', nullable: true),
            new OA\Property(property: 'ramp_down', type: 'double', nullable: true),
            new OA\Property(property: 'post_ramp_down', type: 'double', nullable: true),
        ], type: 'object'),
        new OA\Property(
            property: 'modes',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/quantum_drive_modes_v2')
        ),
    ],
    type: 'object'
)]
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
