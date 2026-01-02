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
        $ports = Arr::get($stdItem, 'Ports', []);

        if (! is_array($ports) || $ports === []) {
            return [];
        }

        return [
            'missile_count' => count($ports),
            'missile_size' => Arr::get($ports, '0.MaxSize', Arr::get($ports, '0.Size')),
        ];
    }
}
