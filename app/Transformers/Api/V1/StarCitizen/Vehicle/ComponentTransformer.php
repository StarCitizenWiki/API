<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Component\Component;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_component',
    title: 'Vehicle Component',
    description: 'Components from in-game files',
    properties: [
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'mounts', type: 'integer'),
        new OA\Property(property: 'component_size', type: 'integer'),
        new OA\Property(property: 'category', type: 'string'),
        new OA\Property(property: 'size', type: 'integer'),
        new OA\Property(property: 'details', type: 'string'),
        new OA\Property(property: 'quantity', type: 'integer'),
        new OA\Property(property: 'manufacturer', type: 'string'),
        new OA\Property(property: 'component_class', type: 'string'),
    ],
    type: 'object'
)]
class ComponentTransformer extends V1Transformer
{
    public function transform(Component $component): array
    {
        return [
            'type' => $component->type,
            'name' => $component->name,
            'mounts' => $component->pivot->mounts,
            'component_size' => $component->component_size,
            'category' => $component->category,
            'size' => $component->pivot->size,
            'details' => $component->pivot->details,
            'quantity' => $component->pivot->quantity,
            'manufacturer' => $component->manufacturer,
            'component_class' => $component->component_class,
        ];
    }
}
