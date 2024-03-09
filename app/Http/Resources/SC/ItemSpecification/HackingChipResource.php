<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'hacking_chip_v2',
    title: 'Hacking Chip',
    properties: [
        new OA\Property(property: 'max_charges', type: 'double', nullable: true),
        new OA\Property(property: 'duration_multiplier', type: 'double', nullable: true),
        new OA\Property(property: 'error_chance', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class HackingChipResource extends AbstractBaseResource
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
            'max_charges' => $this->max_charges,
            'duration_multiplier' => $this->duration_multiplier,
            'error_chance' => $this->error_chance,
        ];
    }
}
