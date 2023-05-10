<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'fuel_intake_v2',
    title: 'Fuel Intake',
    properties: [
        new OA\Property(property: 'fuel_push_rate', type: 'double', nullable: true),
        new OA\Property(property: 'minimum_rate', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class FuelIntakeResource extends AbstractBaseResource
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
            'fuel_push_rate' => $this->fuel_push_rate,
            'minimum_rate' => $this->minimum_rate,
        ];
    }
}
