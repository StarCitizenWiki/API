<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'fuel_tank_v2',
    title: 'Fuel Tank',
    properties: [
        new OA\Property(property: 'fill_rate', type: 'double', nullable: true),
        new OA\Property(property: 'drain_rate', type: 'double', nullable: true),
        new OA\Property(property: 'capacity', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class FuelTankResource extends AbstractBaseResource
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
            'fill_rate' => $this->fill_rate,
            'drain_rate' => $this->drain_rate,
            'capacity' => $this->capacity,
        ];
    }
}
