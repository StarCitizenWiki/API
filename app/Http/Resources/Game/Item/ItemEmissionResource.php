<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_emission',
    title: 'Item EM / IR Emission',
    description: 'EM and IR Emission calculated from Resource Network',
    properties: [
        new OA\Property(property: 'ir', description: 'IR emission', type: 'double', nullable: true),
        new OA\Property(property: 'em_min', description: 'Minimum EM emission', type: 'double', nullable: true),
        new OA\Property(property: 'em_max', description: 'Maximum EM emission', type: 'double', nullable: true),
        new OA\Property(property: 'em_decay', description: 'EM decay', type: 'double', nullable: true),
        new OA\Property(property: 'em_per_segment', description: 'EM decay per segment', type: 'double', nullable: true),
    ],
    type: 'object'
)]
/** @param array $resource Raw emission data from stdItem.Emission sub-array */
class ItemEmissionResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'ir' => Arr::get($this, 'Ir'),
            'em_min' => Arr::get($this, 'Em.Minimum'),
            'em_max' => Arr::get($this, 'Em.Maximum'),
            'em_decay' => Arr::get($this, 'Em.Decay'),
            $this->mergeWhen(Arr::has($this, 'Em.PerSegment'), fn () => ['em_per_segment' => Arr::get($this, 'Em.PerSegment')]),
        ];
    }
}
