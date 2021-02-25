<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\CelestialObject\CelestialObjectSubtype;
use League\Fractal\TransformerAbstract;

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
