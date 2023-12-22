<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'salvage_modifier',
    title: 'Salvage Modifier',
    properties: [
        new OA\Property(property: 'salvage_speed_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'radius_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'extraction_efficiency', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class SalvageModifierResource extends AbstractBaseResource
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
            'salvage_speed_multiplier' => $this->salvage_speed_multiplier,
            'radius_multiplier' => $this->radius_multiplier,
            'extraction_efficiency' => $this->extraction_efficiency,
        ];
    }
}
