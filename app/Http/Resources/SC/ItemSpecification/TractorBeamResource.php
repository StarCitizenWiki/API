<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'tractor_beam_v2',
    title: 'Tractor Beam',
    properties: [
        new OA\Property(property: 'min_force', type: 'double', nullable: true),
        new OA\Property(property: 'max_force', type: 'double', nullable: true),
        new OA\Property(property: 'min_distance', type: 'double', nullable: true),
        new OA\Property(property: 'max_distance', type: 'double', nullable: true),
        new OA\Property(property: 'full_strength_distance', type: 'double', nullable: true),
        new OA\Property(property: 'max_angle', type: 'double', nullable: true),
        new OA\Property(property: 'max_volume', type: 'double', nullable: true),
        new OA\Property(property: 'volume_force_coefficient', type: 'double', nullable: true),
        new OA\Property(property: 'tether_break_time', type: 'double', nullable: true),
        new OA\Property(property: 'safe_range_value_factor', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class TractorBeamResource extends AbstractBaseResource
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
            'min_force' => $this->min_force,
            'max_force' => $this->max_force,
            'min_distance' => $this->min_distance,
            'max_distance' => $this->max_distance,
            'full_strength_distance' => $this->full_strength_distance,
            'max_angle' => $this->max_angle,
            'max_volume' => $this->max_volume,
            'volume_force_coefficient' => $this->volume_force_coefficient,
            'tether_break_time' => $this->tether_break_time,
            'safe_range_value_factor' => $this->safe_range_value_factor,
        ];
    }
}
