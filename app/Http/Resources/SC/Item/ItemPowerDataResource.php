<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_power_data_v2',
    title: 'Item Power Data',
    properties: [
        new OA\Property(property: 'power_base', type: 'double', nullable: true),
        new OA\Property(property: 'power_draw', type: 'double', nullable: true),
        new OA\Property(property: 'throttleable', type: 'boolean', nullable: true),
        new OA\Property(property: 'overclockable', type: 'boolean', nullable: true),
        new OA\Property(property: 'overclock_threshold_min', type: 'double', nullable: true),
        new OA\Property(property: 'overclock_threshold_max', type: 'double', nullable: true),
        new OA\Property(property: 'overclock_performance', type: 'double', nullable: true),
        new OA\Property(property: 'overpower_performance', type: 'double', nullable: true),
        new OA\Property(property: 'power_to_em', type: 'double', nullable: true),
        new OA\Property(property: 'decay_rate_em', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ItemPowerDataResource extends AbstractBaseResource
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
            'power_base' => $this->power_base,
            'power_draw' => $this->power_draw,
            'throttleable' => $this->throttleable,
            'overclockable' => $this->overclockable,
            'overclock_threshold_min' => $this->overclock_threshold_min,
            'overclock_threshold_max' => $this->overclock_threshold_max,
            'overclock_performance' => $this->overclock_performance,
            'overpower_performance' => $this->overpower_performance,
            'power_to_em' => $this->power_to_em,
            'decay_rate_em' => $this->decay_rate_em,
        ];
    }
}
