<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Ammunition;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ammunition_piercability_v2',
    title: 'Ammunition Piercability',
    properties: [
        new OA\Property(property: 'damage_falloff_level_1', type: 'double', nullable: true),
        new OA\Property(property: 'damage_falloff_level_2', type: 'double', nullable: true),
        new OA\Property(property: 'damage_falloff_level_3', type: 'double', nullable: true),
        new OA\Property(property: 'max_penetration_thickness', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class AmmunitionPiercabilityResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'damage_falloff_level_1' => $this->damage_falloff_level_1 ?? null,
            'damage_falloff_level_2' => $this->damage_falloff_level_2 ?? null,
            'damage_falloff_level_3' => $this->damage_falloff_level_3 ?? null,
            'max_penetration_thickness' => $this->max_penetration_thickness ?? null,
        ];
    }
}
