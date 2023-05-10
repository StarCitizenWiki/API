<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\MiningLaser;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_laser_v2',
    title: 'Mining Laser',
    properties: [
        new OA\Property(property: 'power_transfer', type: 'string', nullable: true),
        new OA\Property(property: 'optimal_range', type: 'double', nullable: true),
        new OA\Property(property: 'maximum_range', type: 'double', nullable: true),
        new OA\Property(property: 'extraction_throughput', type: 'double', nullable: true),
        new OA\Property(property: 'module_slots', type: 'integer', nullable: true),
        new OA\Property(
            property: 'modifiers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mining_laser_modifiers_v2')
        ),
    ],
    type: 'object'
)]
class MiningLaserResource extends AbstractBaseResource
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
            'power_transfer' => $this->power_transfer,
            'optimal_range' => $this->optimal_range,
            'maximum_range' => $this->maximum_range,
            'extraction_throughput' => $this->extraction_throughput,
            'module_slots' => $this->module_slots,
            'modifiers' => MiningLaserModifierResource::collection($this->whenLoaded('modifiers')),
        ];
    }
}
