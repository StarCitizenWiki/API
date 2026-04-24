<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactapedia_property',
    title: 'Galactapedia article property',
    description: 'Property of an article',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'value', type: 'string'),
    ],
    type: 'object'
)]
class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'value' => $this->content,
        ];
    }
}
