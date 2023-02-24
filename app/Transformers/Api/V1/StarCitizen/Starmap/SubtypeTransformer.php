<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\CelestialObject\CelestialObjectSubtype;
use League\Fractal\TransformerAbstract;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'celestial_object_subtype',
    title: 'Celestial Object Subtype',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
    ],
    type: 'object'
)]
class SubtypeTransformer extends TransformerAbstract
{
    public function transform(?CelestialObjectSubtype $subtype): array
    {
        if ($subtype === null) {
            return [];
        }

        return [
            'id' => $subtype->id,
            'name' => $subtype->name,
            'type' => $subtype->type,
        ];
    }
}
