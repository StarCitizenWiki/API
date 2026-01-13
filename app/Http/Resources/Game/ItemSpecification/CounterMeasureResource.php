<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'counter_measure',
    title: 'Counter Measure',
    description: 'Counter measure launcher ammo capacity values derived from stdItem.Ammunition or raw ammo container data.',
    properties: [
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'signature', type: 'object', nullable: true),
    ],
    type: 'object'
)]
class CounterMeasureResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);

        return [
            'type' => Arr::get($stdItem, 'WeaponDefensive.Type'),
            'signature' => [
                'infrared' => Arr::get($stdItem, 'WeaponDefensive.Signatures.Infrared.End'),
                'cross_section' => Arr::get($stdItem, 'WeaponDefensive.Signatures.CrossSection.End'),
                'electromagnetic' => Arr::get($stdItem, 'WeaponDefensive.Signatures.Electromagnetic.End'),
                'decibel' => Arr::get($stdItem, 'WeaponDefensive.Signatures.Decibel.End'),
            ],
        ];
    }
}
