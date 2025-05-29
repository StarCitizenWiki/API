<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_cargo_grid',
    title: 'Vehicle Cargo Grid',
    properties: [
        new OA\Property(property: 'capacity', type: 'number'),
        new OA\Property(property: 'capacity_name', type: 'string'),
        new OA\Property(property: 'is_open', type: 'boolean'),
        new OA\Property(property: 'is_external', type: 'boolean'),
        new OA\Property(property: 'is_closed', type: 'boolean'),
        new OA\Property(property: 'x', type: 'number'),
        new OA\Property(property: 'y', type: 'number'),
        new OA\Property(property: 'z', type: 'number'),
        new OA\Property(
            property: 'min_size',
            properties: [
                new OA\Property(property: 'x', type: 'number'),
                new OA\Property(property: 'y', type: 'number'),
                new OA\Property(property: 'z', type: 'number'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'max_size',
            properties: [
                new OA\Property(property: 'x', type: 'number'),
                new OA\Property(property: 'y', type: 'number'),
                new OA\Property(property: 'z', type: 'number'),
            ],
            type: 'object'
        ),
    ],
    type: 'object'
)]
class VehicleCargoGrid extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'capacity' => $this->capacity,
            'capacity_name' => $this->unit_name,
            'is_open' => $this->is_open,
            'is_external' => $this->is_external,
            'is_closed' => $this->is_closed,
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
            'min_size' => [
                'x' => $this->min_x,
                'y' => $this->min_y,
                'z' => $this->min_z,
            ],
            'max_size' => [
                'x' => $this->max_x,
                'y' => $this->max_y,
                'z' => $this->max_z,
            ],
        ];
    }
}
