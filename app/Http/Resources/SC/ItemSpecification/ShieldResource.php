<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shield_v2',
    title: 'Shield',
    properties: [
        new OA\Property(property: 'max_shield_health', type: 'double', nullable: true),
        new OA\Property(property: 'max_shield_regen', type: 'double', nullable: true),
        new OA\Property(property: 'decay_ratio', type: 'double', nullable: true),
        new OA\Property(property: 'regen_delay', properties: [
            new OA\Property(property: 'damage', type: 'double', nullable: true),
            new OA\Property(property: 'downed', type: 'double', nullable: true),
        ], type: 'object'),
    ],
    type: 'object'
)]
class ShieldResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'max_shield_health' => $this->max_shield_health,
            'max_shield_regen' => $this->max_shield_regen,
            'decay_ratio' => $this->decay_ratio,
            'regen_delay' => [
                'downed' => $this->downed_regen_delay,
                'damage' => $this->damage_regen_delay,
            ],
        ];
    }
}
