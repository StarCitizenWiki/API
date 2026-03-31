<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_sku',
    title: 'Vehicle SKU',
    properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'price', type: 'integer'),
        new OA\Property(property: 'available', type: 'boolean'),
        new OA\Property(property: 'imported_at', type: 'datetime'),
    ],
    type: 'object'
)]
class VehicleSkuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'price' => $this->price,
            'available' => (bool) $this->available,
            'imported_at' => $this->created_at,
        ];
    }
}
