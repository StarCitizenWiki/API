<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Starmap;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'affiliation',
    title: 'Affiliation',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'color', type: 'string'),
    ],
    type: 'object'
)]
class AffiliationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cig_id,
            'name' => $this->name,
            'code' => $this->code,
            'color' => $this->color,
        ];
    }
}
