<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'self_destruct_v2',
    title: 'Self Destruct',
    properties: [
        new OA\Property(property: 'damage', type: 'string', nullable: true),
        new OA\Property(property: 'radius', type: 'double', nullable: true),
        new OA\Property(property: 'min_radius', type: 'double', nullable: true),
        new OA\Property(property: 'phys_radius', type: 'double', nullable: true),
        new OA\Property(property: 'min_phys_radius', type: 'double', nullable: true),
        new OA\Property(property: 'time', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class SelfDestructResource extends AbstractBaseResource
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
            'damage' => $this->damage,
            'radius' => $this->radius,
            'min_radius' => $this->min_radius,
            'phys_radius' => $this->phys_radius,
            'min_phys_radius' => $this->min_phys_radius,
            'time' => $this->time,
        ];
    }
}
