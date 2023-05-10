<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactapedia_category_v2',
    title: 'Galctapedia article category',
    description: 'Category of an article',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
    ],
    type: 'object'
)]
class CategoryResource extends JsonResource
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
            'id' => $this->cig_id,
            'name' => $this->name,
        ];
    }
}
