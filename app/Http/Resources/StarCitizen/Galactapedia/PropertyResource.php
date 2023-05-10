<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactapedia_property_v2',
    title: 'Galctapedia article property',
    description: 'Property of an article',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'value', type: 'string'),
    ],
    type: 'object'
)]
class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'value' => $this->content,
        ];
    }
}
