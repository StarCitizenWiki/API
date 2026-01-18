<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_laser',
    title: 'Mining Laser',
    description: 'Mining laser specifications sourced from stdItem.MiningLaser. Includes power band, ranges, extraction throughput, throttle handling, charge modifiers, and module slots. Legacy v2 fields are still returned for backward compatibility (marked deprecated).',
    properties: [
        new OA\Property(
            property: 'mining_power',
            description: 'Minimum and maximum mining laser power drawn from MinPowerTransfer and PowerTransfer. Useful for gauging headroom and overclock potential.',
            properties: [
                new OA\Property(property: 'min', type: 'double', example: 420.0, nullable: true),
                new OA\Property(property: 'max', type: 'double', example: 2100.0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'ranges',
            properties: [
                new OA\Property(property: 'optimal', description: 'Optimal mining distance in meters.', type: 'double', example: 45.0, nullable: true),
                new OA\Property(property: 'maximum', description: 'Maximum effective range in meters.', type: 'double', example: 135.0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'extraction',
            description: 'Extraction or collection throughput metrics relevant to rock or ore collection.',
            properties: [
                new OA\Property(property: 'throughput_scu_per_s', description: 'Throughput in SCU/s (ExtractionThroughput or Collection Throughput).', type: 'double', example: 2775.0, nullable: true),
                new OA\Property(property: 'collection_point_radius_m', description: 'Radius of the collection point in meters when provided by modifiers or description data.', type: 'double', example: 0.1, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'handling',
            description: 'Throttle behaviour values that affect how quickly power adjustments apply.',
            properties: [
                new OA\Property(property: 'uses_power_throttle', type: 'boolean', example: false, nullable: true),
                new OA\Property(property: 'throttle_speed', description: 'Rate at which throttle can change (higher is snappier).', type: 'double', example: 6.5, nullable: true),
                new OA\Property(property: 'throttle_responsiveness_delay', description: 'Delay before throttle responsiveness kicks in (seconds).', type: 'double', example: 0.2, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'modifiers',
            description: 'Gameplay-impacting modifiers applied by the mining laser baseline.',
            properties: [
                new OA\Property(property: 'optimal_charge_window_percent', description: 'Change to optimal charge window size.', type: 'double', example: 20.0, nullable: true),
                new OA\Property(property: 'optimal_charge_rate_percent', description: 'Change to optimal charge rate.', type: 'double', example: -40.0, nullable: true),
                new OA\Property(property: 'all_charge_rates_percent', description: 'Flat change to all charge rates.', type: 'double', example: 30.0, nullable: true),
                new OA\Property(property: 'instability_percent', description: 'Change to laser instability.', type: 'double', example: -10.0, nullable: true),
                new OA\Property(property: 'resistance_percent', description: 'Change to deposit resistance.', type: 'double', example: 10.0, nullable: true),
                new OA\Property(property: 'collection_point_radius_m', description: 'Collection point radius when provided via modifiers.', type: 'double', example: 0.1, nullable: true),
                new OA\Property(property: 'throttle_responsiveness_delay', description: 'Modifier-provided throttle delay.', type: 'double', example: 0.2, nullable: true),
                new OA\Property(property: 'throttle_speed', description: 'Modifier-provided throttle speed.', type: 'double', example: 6.5, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'module_slots', description: 'Number of mining module/consumable slots. Falls back to counting mining modifier ports when ModuleSlots is absent.', type: 'integer', example: 2, nullable: true),
        // Legacy / backward-compatibility (deprecated)
        new OA\Property(property: 'power_transfer', description: 'Deprecated: original v2 power_transfer string.', type: 'string', example: '420 - 2100', nullable: true, deprecated: true),
        new OA\Property(property: 'optimal_range', description: 'Deprecated: use ranges.optimal.', type: 'double', example: 45.0, nullable: true, deprecated: true),
        new OA\Property(property: 'maximum_range', description: 'Deprecated: use ranges.maximum.', type: 'double', example: 135.0, nullable: true, deprecated: true),
        new OA\Property(property: 'extraction_throughput', description: 'Deprecated: use extraction.throughput_scu_per_s.', type: 'double', example: 2775.0, nullable: true, deprecated: true),
        new OA\Property(property: 'extraction_laser_power', description: 'Deprecated: source description Extraction Laser Power string.', type: 'string', example: '2775', nullable: true, deprecated: true),
        new OA\Property(property: 'mining_laser_power', description: 'Deprecated: description Mining Laser Power string.', type: 'string', example: '420 - 2100', nullable: true, deprecated: true),
        new OA\Property(property: 'modifiers_legacy', description: 'Deprecated: flattened modifiers for v2 parity.', type: 'object', nullable: true, deprecated: true),
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
                'minimum' => Arr::get($miningLaser, 'MinPowerTransfer'),
                'maximum' => Arr::get($miningLaser, 'PowerTransfer'),
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
