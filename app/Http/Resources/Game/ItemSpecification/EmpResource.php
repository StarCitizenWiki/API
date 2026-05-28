<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'emp',
    title: 'EMP Generator',
    description: 'Electromagnetic pulse generator stats pulled from Item.stdItem.Emp. Values scale with generator size (S1-S4) and determine how quickly the device charges, how hard it hits shields via distortion, and how far the blast reaches.',
    properties: [
        new OA\Property(
            property: 'distortion_damage',
            description: 'Peak distortion damage applied at the center of the blast. Observed values: 1000 (S1), 1800-2475 (S2-S3), 2750-3300 (S4 variants).',
            type: 'double',
            example: 1000.0,
            nullable: true
        ),
        new OA\Property(
            property: 'emp_radius',
            description: 'Maximum effective distortion radius in meters. Current items range from 400m (S1) up to 1100m (large ship EMPs).',
            type: 'double',
            example: 400.0,
            nullable: true,
            x: ['suffix' => ' m']
        ),
        new OA\Property(
            property: 'min_emp_radius',
            description: 'Inner radius (m) guaranteed to receive full EMP effect before falloff begins. Presently 150m-250m.',
            type: 'double',
            example: 150.0,
            nullable: true,
            x: ['suffix' => ' m']
        ),

        new OA\Property(
            property: 'charge_duration',
            description: 'Seconds required to fully charge before firing. Current game data ranges 12-26 seconds across EMP sizes.',
            type: 'double',
            example: 12.0,
            nullable: true,
            x: ['suffix' => ' s']
        ),
        new OA\Property(
            property: 'unleash_duration',
            description: 'Duration in seconds the pulse is actively released after charging completes. Values are either 0.75s (small) or 1.5s (large).',
            type: 'double',
            example: 0.75,
            nullable: true,
            x: ['suffix' => ' s']
        ),
        new OA\Property(
            property: 'cooldown_duration',
            description: 'Cooldown in seconds before charging can restart. Current data spans 6-40 seconds depending on size and variant.',
            type: 'double',
            example: 6.0,
            nullable: true,
            x: ['suffix' => ' s']
        ),
    ],
    type: 'object'
)]
class EmpResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $emp = Arr::get($data, 'stdItem.Emp', []);

        $chargeDuration = Arr::get($emp, 'ChargeTime');
        $unleashDuration = Arr::get($emp, 'UnleashTime');
        $cooldownDuration = Arr::get($emp, 'CooldownTime');

        return [
            'distortion_damage' => Arr::get($emp, 'DistortionDamage'),
            'emp_radius' => Arr::get($emp, 'EmpRadius'),
            'min_emp_radius' => Arr::get($emp, 'MinEmpRadius'),

            'charge_duration' => $chargeDuration,
            'unleash_duration' => $unleashDuration,
            'cooldown_duration' => $cooldownDuration,
        ];
    }
}
