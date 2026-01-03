<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactapedia_tag',
    title: 'Galctapedia article tag',
    description: 'Tag of an article',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
    ],
    type: 'object'
)]
class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cig_id,
            'name' => $this->name,
        ];
    }
}
