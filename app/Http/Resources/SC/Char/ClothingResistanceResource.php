<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'clothing_resistance_v2',
    title: 'Clothing Resistance',
    description: 'Resistance of Clothes or Armors',
    properties: [
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'threshold', type: 'double', nullable: true),
        new OA\Property(property: 'multiplier', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ClothingResistanceResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'type' => $this->type,
            'threshold' => $this->threshold,
            'multiplier' => $this->multiplier,
        ];
    }
}
