<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_temperature',
    title: 'Item Temperature',
    description: 'Calculated temperature information, generated from Temperature attributes.',
    properties: [
        new OA\Property(property: 'cooling_threshold', description: 'Calculated cooling threshold', type: 'double', nullable: true),
        new OA\Property(property: 'ir_threshold', description: 'Calculated IR threshold', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_threshold', description: 'Calculated overheat threshold', type: 'double', nullable: true),
        new OA\Property(property: 'max_temperature', description: 'Calculated maximum temperature', type: 'double', nullable: true),
        new OA\Property(property: 'recovery_temperature', description: 'Calculated recovery temperature', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class ItemTemperatureResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'unit' => Arr::get($this, 'Calculated.Unit'),
            'cooling_threshold' => Arr::get($this, 'Calculated.CoolingThreshold'),
            'ir_threshold' => Arr::get($this, 'Calculated.IrThreshold'),
            'overheat_temperature' => Arr::get($this, 'Calculated.Overheat'),
            'max_temperature' => Arr::get($this, 'Calculated.Max'),
            'recovery_temperature' => Arr::get($this, 'Calculated.Recovery'),
        ];
    }
}
