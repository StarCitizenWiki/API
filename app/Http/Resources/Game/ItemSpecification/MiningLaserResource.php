<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_laser_power_band',
    title: 'Mining Laser Power Band',
    description: 'Minimum/maximum laser power values as provided by stdItem.MiningLaser.',
    properties: [
        new OA\Property(property: 'min', description: 'Minimum power transfer (MinPowerTransfer).', type: 'double', example: 420.0, nullable: true),
        new OA\Property(property: 'max', description: 'Maximum power transfer (PowerTransfer).', type: 'double', example: 2100.0, nullable: true),
        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', example: 420.0, nullable: true, deprecated: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', example: 2100.0, nullable: true, deprecated: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mining_laser_modifier',
    title: 'Mining Laser Modifier',
    description: 'Single modifier entry derived from the modifier block (only emitted when value is not null).',
    properties: [
        new OA\Property(property: 'name', description: 'Internal modifier key.', type: 'string', example: 'optimal_charge_rate', nullable: false),
        new OA\Property(property: 'display_name', description: 'Human-readable name derived from the key.', type: 'string', example: 'Optimal Charge Rate', nullable: false),
        new OA\Property(property: 'value', description: 'Numeric modifier value (percentage points where applicable).', type: 'double', example: -40.0, nullable: false),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mining_laser',
    title: 'Mining Laser',
    description: 'Mining laser specifications sourced from stdItem.MiningLaser. Includes power band, ranges, extraction throughput, throttle handling, modifiers, and module slots. Legacy v2 fields are still returned for backward compatibility (marked deprecated).',
    properties: [
        new OA\Property(
            property: 'laser_power',
            ref: '#/components/schemas/mining_laser_power_band',
            description: 'Minimum/maximum mining laser power drawn from MinPowerTransfer and PowerTransfer.'
        ),

        new OA\Property(
            property: 'modifiers',
            description: 'Deprecated: Use modifier_map. List of non-null gameplay modifiers derived from stdItem.MiningLaser.Modifiers.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mining_laser_modifier'),
            deprecated: true
        ),

        new OA\Property(
            property: 'module_slots',
            description: 'Number of mining module/consumable slots (ModuleSlots).',
            type: 'integer',
            example: 2,
            nullable: true
        ),

        new OA\Property(
            property: 'throttle_lerp_speed',
            description: 'Throttle lerp speed (ThrottleLerpSpeed).',
            type: 'double',
            example: 6.5,
            nullable: true
        ),
        new OA\Property(
            property: 'throttle_minimum',
            description: 'Minimum throttle value (ThrottleMinimum).',
            type: 'double',
            example: 0.1,
            nullable: true
        ),

        new OA\Property(
            property: 'power_transfer',
            description: 'Deprecated. Use `laser_power.maximum`.',
            type: 'double',
            example: 2100.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'mining_laser_power',
            description: 'Deprecated. Use `laser_power.minimum` and `laser_power.maximum` (this is a formatted string range).',
            type: 'string',
            example: '420 - 2100',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'modifier_map',
            type: 'object',
            nullable: false,
            additionalProperties: new OA\AdditionalProperties(type: 'number'),
        ),
        new OA\Property(
            property: 'extraction_laser_power',
            description: 'Deprecated. Prefer `extraction_throughput` when available (this value is taken from description text).',
            type: 'string',
            example: '2775',
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'optimal_range',
            description: 'Optimal mining distance in meters (OptimalRange).',
            type: 'double',
            example: 45.0,
            nullable: true
        ),
        new OA\Property(
            property: 'maximum_range',
            description: 'Maximum effective range in meters (MaximumRange).',
            type: 'double',
            example: 135.0,
            nullable: true
        ),
        new OA\Property(
            property: 'extraction_throughput',
            description: 'Extraction throughput metric (ExtractionThroughput).',
            type: 'double',
            example: 2775.0,
            nullable: true
        ),
        new OA\Property(
            property: 'collection_point_radius',
            description: 'Radius of the collection point beam (CollectionPointRadius).',
            type: 'double',
            example: 0.1,
            nullable: true
        ),
    ],
    type: 'object'
)]
class MiningLaserResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $description = Arr::get($stdItem, 'DescriptionData', []);
        $miningLaser = Arr::get($stdItem, 'MiningLaser', []);
        $modifiers = is_array($miningLaser) ? Arr::get($miningLaser, 'Modifiers', []) : [];

        $modifierBlock = [
            'resistance' => $this->toFloat(Arr::get($modifiers, 'Resistance')),
            'laser_instability' => $this->toFloat(Arr::get($modifiers, 'Instability')),
            'optimal_charge_window_size' => $this->toFloat(Arr::get($modifiers, 'OptimalChargeWindow')),
            'optimal_charge_rate' => $this->toFloat(Arr::get($modifiers, 'OptimalChargeRate')),
            'inert_materials' => $this->toFloat(Arr::get($modifiers, 'InertMaterials')),

            'all_charge_rates' => $this->toFloat(Arr::get($modifiers, 'AllChargeRates')),
        ];

        return [
            'laser_power' => [
                'min' => Arr::get($miningLaser, 'MinPowerTransfer'),
                'max' => Arr::get($miningLaser, 'PowerTransfer'),
                'minimum' => Arr::get($miningLaser, 'MinPowerTransfer'),  // deprecated: use min
                'maximum' => Arr::get($miningLaser, 'PowerTransfer'),  // deprecated: use max
            ],

            'modifiers' => collect($modifierBlock)
                ->map(static fn ($value, $key) => [
                    'name' => $key,
                    'display_name' => Str::of($key)->snake()->replace('_', ' ')->title()->toString(),
                    'value' => $value,
                ])
                ->filter(static fn ($value) => $value['value'] !== null)
                ->values()
                ->toArray(),

            'module_slots' => Arr::get($miningLaser, 'ModuleSlots'),

            'throttle_lerp_speed' => Arr::get($miningLaser, 'ThrottleLerpSpeed'),
            'throttle_minimum' => Arr::get($miningLaser, 'ThrottleMinimum'),

            'power_transfer' => Arr::get($miningLaser, 'PowerTransfer'),

            'optimal_range' => Arr::get($miningLaser, 'OptimalRange'),
            'maximum_range' => Arr::get($miningLaser, 'MaximumRange'),

            'extraction_throughput' => Arr::get($miningLaser, 'ExtractionThroughput'),
            'collection_point_radius' => Arr::get($miningLaser, 'CollectionPointRadius'),
            'extraction_laser_power' => Arr::get($description, 'Extraction Laser Power'),
            'mining_laser_power' => $this->formatPowerRange(
                Arr::get($miningLaser, 'MinPowerTransfer'),
                Arr::get($miningLaser, 'PowerTransfer'),
            ),

            'modifier_map' => array_filter($modifierBlock, static fn ($value) => $value !== null),
        ];
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = str_replace('%', '', $value);

            if (is_numeric($normalized)) {
                return (float) $normalized;
            }

            if (preg_match('/(-?\d+(?:\.\d+)?)/', $value, $matches)) {
                return (float) $matches[1];
            }
        }

        return null;
    }

    private function formatPowerRange(?float $min, ?float $max): ?string
    {
        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null) {
            return sprintf('%d - %d', $min, $max);
        }

        return (string) ($max ?? $min);
    }
}
