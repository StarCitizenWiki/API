<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_module',
    title: 'Mining Module',
    description: 'Active and passive mining modules pulled from Item.stdItem.MiningModule in game data (Raw ignored). Focuses on player-facing usability (uses, duration, charges, lifetime) and modifiers that affect mining difficulty, yield quality, and safety. Legacy top-level fields remain for backwards compatibility; prefer the nested usage/modifiers objects.',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'Mining Module (Active)', nullable: true),
        new OA\Property(property: 'module_type', type: 'string', example: 'Active', nullable: true),
        new OA\Property(
            property: 'usage',
            description: 'Usability details sourced from Item.stdItem.MiningModule (charges/lifetime) and Item.stdItem.DescriptionData (uses/duration).',
            properties: [
                new OA\Property(property: 'charges', description: 'Total activations provided by the module.', type: 'integer', example: 6, nullable: true),
                new OA\Property(property: 'lifetime_seconds', description: 'Lifetime in seconds a module remains effective once installed.', type: 'double', example: 60, nullable: true),
                new OA\Property(property: 'uses', description: 'Number of activations before a consumable expires. Observed range: 3–10.', type: 'integer', example: 6, nullable: true),
                new OA\Property(property: 'duration_seconds', description: 'Active duration per use in seconds. Observed range: 15–60.', type: 'double', example: 30, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'charges',
            description: 'Deprecated: use usage.charges.',
            type: 'integer',
            example: 5,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'lifetime_seconds',
            description: 'Deprecated: use usage.lifetime_seconds.',
            type: 'double',
            example: 3600,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'uses',
            description: 'Deprecated: use usage.uses.',
            type: 'integer',
            example: 5,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'duration_seconds',
            description: 'Deprecated: use usage.duration_seconds.',
            type: 'double',
            example: 60,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'modifiers',
            description: 'Gameplay-impacting modifiers applied while the module is active or installed.',
            properties: [
                new OA\Property(property: 'mining_laser_power_percent', description: 'Multiplicative change to mining laser power. 150 boosts power by 50%; 85 lowers by 15%.', type: 'double', example: 135, nullable: true),
                new OA\Property(property: 'extraction_laser_power_percent', description: 'Multiplier on extraction beam power. Lower values mean slower extraction but often reduce inert material.', type: 'double', example: 95, nullable: true),
                new OA\Property(property: 'optimal_charge_window_percent', description: 'Change to the size of the optimal (green) window. Positive widens, negative shrinks. Range seen: -10 to +40.', type: 'double', example: 40, nullable: true),
                new OA\Property(property: 'optimal_charge_rate_percent', description: 'Change to the ideal charge rate speed. Higher values speed charging; negatives slow it. Range seen: -20 to +60.', type: 'double', example: 60, nullable: true),
                new OA\Property(property: 'all_charge_rates_percent', description: 'Flat multiplier to all charge-rate bands when present. Range seen: +5 to +24.', type: 'double', example: 24, nullable: true),
                new OA\Property(property: 'inert_material_modifier_percent', description: 'Reduction to inert material collected. Positive values in data mean less inert material (e.g., 6, 24).', type: 'double', example: 24, nullable: true),
                new OA\Property(property: 'resistance_percent', description: 'Change to deposit resistance (hardness). Positive makes rock more resistant; negative softens. Range seen: -24.8 to +15.5.', type: 'double', example: -15, nullable: true),
                new OA\Property(property: 'instability_percent', description: 'Change to laser instability. Negative stabilizes, positive increases jitter. Range seen: -20 to +10.', type: 'double', example: -10, nullable: true),
                new OA\Property(property: 'shatter_damage_percent', description: 'Change to damage taken when the rock shatters. Negative reduces hazard; positive increases. Range seen: -30 to +40.', type: 'double', example: -30, nullable: true),
                new OA\Property(property: 'overcharge_rate_percent', description: 'Change to catastrophic/overcharge rate. Negative lowers risk, positive increases. Range seen: -80 to +60.', type: 'double', example: -60, nullable: true),
                new OA\Property(property: 'cluster_factor', description: 'Scaling applied to cluster-related calculations for fracture/extraction when present.', type: 'double', example: 1.35, nullable: true),
                new OA\Property(property: 'damage_multiplier', description: 'General damage multiplier used by modules. Observed range: 0.85–1.50.', type: 'double', example: 1.35, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class MiningModuleResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $description = Arr::get($stdItem, 'DescriptionData', []);
        $miningModule = Arr::get($stdItem, 'MiningModule', []);
        $modifiers = is_array($miningModule) ? Arr::get($miningModule, 'Modifiers', []) : [];

        $usage = [
            'charges' => Arr::get($miningModule, 'Charges', Arr::get($stdItem, 'Charges')),
            'lifetime_seconds' => $this->parseSeconds(Arr::get($miningModule, 'Lifetime', Arr::get($stdItem, 'Lifetime'))),
            'uses' => $this->parseInt(Arr::get($description, 'Uses')),
            'duration_seconds' => $this->parseDurationToSeconds(Arr::get($description, 'Duration')),
        ];

        $legacyModifiers = [
            'all_charge_rates' => Arr::get($description, 'All Charge Rates', Arr::get($modifiers, 'AllChargeRates')),
            'collection_point_radius' => Arr::get($description, 'Collection Point Radius', Arr::get($modifiers, 'CollectionPointRadius')),
            'instability' => Arr::get($description, 'Instability', Arr::get($modifiers, 'Instability')),
            'module' => Arr::get($description, 'Module'),
            'optimal_charge_rate' => Arr::get($description, 'Optimal Charge Rate', Arr::get($modifiers, 'OptimalChargeRate')),
            'optimal_charge_window' => Arr::get($description, 'Optimal Charge Window', Arr::get($description, 'Optimal Charge Window Size', Arr::get($modifiers, 'OptimalChargeWindow'))),
            'overcharge_rate' => Arr::get($description, 'Overcharge Rate', Arr::get($description, 'Catastrophic Charge Rate', Arr::get($modifiers, 'OverchargeRate'))),
            'resistance' => Arr::get($description, 'Resistance', Arr::get($modifiers, 'Resistance')),
            'shatter_damage' => Arr::get($description, 'Shatter Damage', Arr::get($modifiers, 'ShatterDamage')),
            'throttle_responsiveness_delay' => Arr::get($description, 'Throttle Responsiveness Delay', Arr::get($modifiers, 'ThrottleResponsivenessDelay')),
            'throttle_speed' => Arr::get($description, 'Throttle Speed', Arr::get($modifiers, 'ThrottleSpeed')),
            'extraction_rate' => Arr::get($description, 'Extraction Rate', Arr::get($modifiers, 'ExtractionRate')),
            'inert_materials' => Arr::get($description, 'Inert Materials', Arr::get($modifiers, 'InertMaterials')),
        ];

        return [
            'type' => Arr::get($description, 'Item Type'),
            'module_type' => Arr::get($miningModule, 'Type'),
            'usage' => $usage,
            // Backwards-compatible top-level fields (prefer usage.*)
            'charges' => $usage['charges'],
            'lifetime_seconds' => $usage['lifetime_seconds'],
            'uses' => $usage['uses'],
            'duration_seconds' => $usage['duration_seconds'],
            'modifier_map' => [
                'mining_laser_power_percent' => $this->parsePercent(Arr::get($description, 'Mining Laser Power')),
                'extraction_laser_power_percent' => $this->parsePercent(Arr::get($description, 'Extraction Laser Power')),
                'optimal_charge_window_percent' => $this->parsePercent(
                    Arr::get($description, 'Optimal Charge Window Size'),
                    Arr::get($modifiers, 'OptimalChargeWindow')
                ),
                'optimal_charge_rate_percent' => $this->parsePercent(
                    Arr::get($description, 'Optimal Charge Rate'),
                    Arr::get($modifiers, 'OptimalChargeRate')
                ),
                'all_charge_rates_percent' => $this->parsePercent(Arr::get($modifiers, 'AllChargeRates')),
                'inert_material_modifier_percent' => $this->parsePercent(
                    Arr::get($description, 'Inert Material Level'),
                    Arr::get($modifiers, 'InertMaterials')
                ),
                'resistance_percent' => $this->parsePercent(
                    Arr::get($description, 'Resistance'),
                    Arr::get($modifiers, 'Resistance')
                ),
                'instability_percent' => $this->parsePercent(
                    Arr::get($description, 'Laser Instability'),
                    Arr::get($modifiers, 'Instability')
                ),
                'shatter_damage_percent' => $this->parsePercent(
                    Arr::get($description, 'Shatter Damage'),
                    Arr::get($modifiers, 'ShatterDamage')
                ),
                'overcharge_rate_percent' => $this->parsePercent(
                    Arr::get($description, 'Catastrophic Charge Rate'),
                    Arr::get($modifiers, 'OverchargeRate')
                ),
                'cluster_factor' => Arr::get($modifiers, 'ClusterFactor'),
                'damage_multiplier' => Arr::get($modifiers, 'DamageMultiplier'),
            ],
            'modifiers' => array_filter($legacyModifiers, static fn ($value) => $value !== null),
        ];
    }

    private function parsePercent(mixed $value, mixed $fallback = null): ?float
    {
        $primary = $this->toFloat($value);

        if ($primary !== null) {
            return $primary;
        }

        return $this->toFloat($fallback);
    }

    private function parseDurationToSeconds(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $float = $this->toFloat($value);

        if ($float !== null) {
            return $float;
        }

        if (is_string($value) && preg_match('/(-?\\d+(?:\\.\\d+)?)/', $value, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    private function parseSeconds(mixed $value): ?float
    {
        return $this->toFloat($value);
    }

    private function parseInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && preg_match('/(-?\\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
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
            if (preg_match('/(-?\\d+(?:\\.\\d+)?)/', $value, $matches)) {
                return (float) $matches[1];
            }
        }

        return null;
    }
}
