<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification\MiningLaser;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_laser_modifiers_v2',
    title: 'Mining Laser Modifiers',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'value'),
    ],
    type: 'object'
)]
class MiningLaserModifierResource extends AbstractBaseResource
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
            'name' => $this->name,
            'value' => $this->modifier,
        ];
    }
}
