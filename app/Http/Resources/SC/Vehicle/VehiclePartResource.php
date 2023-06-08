<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_part_v2',
    title: 'Vehicle Parts',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'display_name', type: 'string'),
        new OA\Property(property: 'children', ref: '#/components/schemas/vehicle_part_v2', nullable: true),
        new OA\Property(property: 'damage_max', type: 'number'),
    ],
    type: 'object'
)]
class VehiclePartResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'display_name' => $this->display_name,
            'damage_max' => $this->damage_max,
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
