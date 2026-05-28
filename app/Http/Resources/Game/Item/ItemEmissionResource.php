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
        new OA\Property(property: 'ir', description: 'Infrared (thermal) emission signature. Higher values make the item easier to detect.', type: 'double', nullable: true, x: ['suffix' => ' IR']),
        new OA\Property(property: 'em_min', description: 'Electromagnetic emission at idle/low power (Maximum * minConsumptionFraction * lowPowerRangeModifier).', type: 'double', nullable: true, x: ['suffix' => ' EM']),
        new OA\Property(property: 'em_max', description: 'Electromagnetic emission at full power from EMSignature.nominalSignature.', type: 'double', nullable: true, x: ['suffix' => ' EM']),
        new OA\Property(property: 'em_decay', description: 'Rate at which EM drops when the item powers down (typically 0.15).', type: 'double', nullable: true, x: ['suffix' => ' /s']),
        new OA\Property(property: 'em_per_segment', description: 'EM per power segment unit (PowerPlant only: Maximum / Generation.Power).', type: 'double', nullable: true, x: ['suffix' => ' EM/Seg']),
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
