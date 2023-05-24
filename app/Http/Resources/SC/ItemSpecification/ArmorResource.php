<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'armor_v2',
    title: 'Vehicle Armor',
    properties: [
        new OA\Property(property: 'signal_infrared', type: 'double', nullable: true),
        new OA\Property(property: 'signal_electromagnetic', type: 'double', nullable: true),
        new OA\Property(property: 'signal_cross_section', type: 'double', nullable: true),
        new OA\Property(property: 'damage_physical', type: 'double', nullable: true),
        new OA\Property(property: 'damage_energy', type: 'double', nullable: true),
        new OA\Property(property: 'damage_distortion', type: 'double', nullable: true),
        new OA\Property(property: 'damage_thermal', type: 'double', nullable: true),
        new OA\Property(property: 'damage_biochemical', type: 'double', nullable: true),
        new OA\Property(property: 'damage_stun', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ArmorResource extends AbstractBaseResource
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
            'signal_infrared' => $this->signal_infrared,
            'signal_electromagnetic' => $this->signal_electromagnetic,
            'signal_cross_section' => $this->signal_cross_section,
            'damage_physical' => $this->damage_physical,
            'damage_energy' => $this->damage_energy,
            'damage_distortion' => $this->damage_distortion,
            'damage_thermal' => $this->damage_thermal,
            'damage_biochemical' => $this->damage_biochemical,
            'damage_stun' => $this->damage_stun,
        ];
    }
}
