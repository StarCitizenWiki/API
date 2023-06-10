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
        new OA\Property(
            property: 'optimal_range',
            description: 'Range in meter',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'maximum_range',
            description: 'Range in meter',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'extraction_throughput',
            description: 'Extraction throughput in "SCU/s"',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'module_slots',
            description: 'Number of mining module slots. Should be equivalent to "max_mounts".',
            type: 'integer',
            nullable: true
        ),
        new OA\Property(
            property: 'modifiers',
            description: 'List of mining modifiers.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mining_laser_modifiers_v2')
        ),
        new OA\Property(
            property: 'extraction_laser_power',
            type: 'string',
            nullable: true,
        ),
        new OA\Property(
            property: 'mining_laser_power',
            type: 'string',
            nullable: true,
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
            'extraction_laser_power' => $this->extraction_laser_power,
            'mining_laser_power' => $this->mining_laser_power,
            'modifiers' => MiningLaserModifierResource::collection($this->modifiers),
        ];
    }
}
