<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'qig_v2',
    title: 'Quantum Interdiction Generator',
    properties: [
        new OA\Property(property: 'jammer_range', type: 'double', nullable: true),
        new OA\Property(property: 'interdiction_range', type: 'double', nullable: true),
        new OA\Property(property: 'charge_duration', type: 'double', nullable: true),
        new OA\Property(property: 'discharge_duration', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown_duration', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class QuantumInterdictionGeneratorResource extends AbstractBaseResource
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
            'jammer_range' => $this->jammer_range,
            'interdiction_range' => $this->interdiction_range,
            'charge_duration' => $this->charge_duration,
            'discharge_duration' => $this->discharge_duration,
            'cooldown_duration' => $this->cooldown_duration,
        ];
    }
}
