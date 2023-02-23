<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactpedia_tag_v2',
    title: 'Galctapedia article tag',
    description: 'Tag of an article',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
    ],
    type: 'json'
)]
class TagResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

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
