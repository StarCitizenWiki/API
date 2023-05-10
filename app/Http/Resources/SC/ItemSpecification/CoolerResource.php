<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'cooler_v2',
    title: 'Cooler',
    properties: [
        new OA\Property(property: 'cooling_rate', type: 'double', nullable: true),
        new OA\Property(property: 'suppression_ir_factor', type: 'double', nullable: true),
        new OA\Property(property: 'suppression_heat_factor', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class CoolerResource extends AbstractBaseResource
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
            'cooling_rate' => $this->cooling_rate,
            'suppression_ir_factor' => $this->suppression_ir_factor,
            'suppression_heat_factor' => $this->suppression_heat_factor,
        ];
    }
}
