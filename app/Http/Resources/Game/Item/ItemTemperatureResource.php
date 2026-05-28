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
    description: 'Temperature thresholds in Celsius, converted from the SEntityPhysicsControllerParams temperature system.',
    properties: [
        new OA\Property(property: 'unit', description: 'Temperature unit (always "C" for Celsius).', type: 'string', nullable: true),
        new OA\Property(property: 'cooling_threshold', description: 'Temperature in Celsius at which active cooling begins.', type: 'double', nullable: true, x: ['suffix' => ' °C']),
        new OA\Property(property: 'ir_threshold', description: 'Temperature in Celsius above which IR emission becomes detectable.', type: 'double', nullable: true, x: ['suffix' => ' °C']),
        new OA\Property(property: 'overheat_threshold', description: 'Temperature in Celsius at which the overheat warning triggers (before full overheat).', type: 'double', nullable: true, x: ['suffix' => ' °C']),
        new OA\Property(property: 'overheat_temperature', description: 'Deprecated: Use overheat_threshold.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'max_temperature', description: 'Temperature in Celsius at which the item fully overheats and shuts down.', type: 'double', nullable: true, x: ['suffix' => ' °C']),
        new OA\Property(property: 'recovery_temperature', description: 'Temperature in Celsius the item must cool to before recovering from overheat.', type: 'double', nullable: true, x: ['suffix' => ' °C']),
    ],
    type: 'object'
)]
/** @param array $resource Raw temperature data from stdItem.Temperature sub-array */
class ItemTemperatureResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'unit' => Arr::get($this, 'Calculated.Unit'),
            'cooling_threshold' => Arr::get($this, 'Calculated.CoolingThreshold'),
            'ir_threshold' => Arr::get($this, 'Calculated.IrThreshold'),
            'overheat_threshold' => Arr::get($this, 'Calculated.Overheat'),
            'overheat_temperature' => Arr::get($this, 'Calculated.Overheat'),  // deprecated: use overheat_threshold
            'max_temperature' => Arr::get($this, 'Calculated.Maximum'),
            'recovery_temperature' => Arr::get($this, 'Calculated.Recovery'),
        ];
    }
}
