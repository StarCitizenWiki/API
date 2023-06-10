<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'emp_v2',
    title: 'EMP',
    properties: [
        new OA\Property(property: 'charge_duration', type: 'double', nullable: true),
        new OA\Property(property: 'emp_radius', type: 'double', nullable: true),
        new OA\Property(property: 'unleash_duration', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown_duration', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class EmpResource extends AbstractBaseResource
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
            'charge_duration' => $this->charge_duration,
            'emp_radius' => $this->emp_radius,
            'unleash_duration' => $this->unleash_duration,
            'cooldown_duration' => $this->cooldown_duration,
        ];
    }
}
