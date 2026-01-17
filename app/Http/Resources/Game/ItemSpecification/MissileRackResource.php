<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'missile_rack',
    title: 'Missile Rack',
    description: 'Missile rack capacity derived from port configuration.',
    properties: [
        new OA\Property(property: 'missile_count', type: 'integer', nullable: true),
        new OA\Property(property: 'missile_size', type: 'integer', nullable: true),
    ],
    type: 'object'
)]
class MissileRackResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);

        return [
            'missile_count' => Arr::get($stdItem, 'MissileRack.MissileCount'),
            'missile_size' => Arr::get($stdItem, 'MissileRack.MissileSize'),
        ];
    }
}
