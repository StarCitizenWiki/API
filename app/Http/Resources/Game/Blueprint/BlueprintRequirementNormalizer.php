<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Blueprint;

use App\Http\Resources\Game\Concerns\NormalizesValues;
use Illuminate\Support\Str;

/**
 * @internal
 *
 * Pure-function pipeline for normalizing blueprint requirements, ingredients,
 * modifiers, and summary properties from raw blueprint payload data.
 */
final class BlueprintRequirementNormalizer
{
    use NormalizesValues;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    public function requirementGroups(array $payload): array
    {
        $groups = [];

        foreach ($this->rootRequirementChildren($payload) as $node) {
            if (! is_array($node)) {
                continue;
            }

            $groups[] = $this->normalizeRequirementGroup($node);
        }

        return $groups;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    /**
     * @param  array<int, array<string, mixed>>  $requirementGroups
     */
    public function summaryPropertiesFromGroups(array $requirementGroups): array
    {
        $summaryProperties = [];

        foreach ($requirementGroups as $group) {
            $this->collectSummaryProperties($summaryProperties, $group['modifiers']);
            $this->collectChildSummaryProperties($summaryProperties, $group['children']);
        }

        return array_values($summaryProperties);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    /**
     * @param  array<int, array<string, mixed>>  $requirementGroups
     */
    public function ingredientCountFromGroups(array $requirementGroups): int
    {
        $ingredientCount = 0;

        foreach ($requirementGroups as $group) {
            $ingredientCount += $this->countRequirementChildren(
                is_array($group['children'] ?? null) ? $group['children'] : [],
            );
        }

        return $ingredientCount;
    }

    /**
     * @param  array<int, array<string, mixed>>  $requirementGroups
     * @param  array<string, array{name: ?string, kind: ?string, resource_type_uuid: ?string, item_uuid: ?string, quantity_scu: int|float|null, quantity: int|float|null, link: ?string, web_url: ?string}>  $ingredients
     */
    public function collectIngredients(array $requirementGroups, array &$ingredients): void
    {
        foreach ($requirementGroups as $group) {
            $this->collectIngredientsFromChildren(
                is_array($group['children'] ?? null) ? $group['children'] : [],
                $ingredients,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, mixed>
     */
    private function rootRequirementChildren(array $payload): array
    {
        $children = data_get($payload, 'tiers.0.requirements.children');

        return is_array($children) ? $children : [];
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function normalizeRequirementGroup(array $node): array
    {
        $kind = $this->arrayNullableString($node, 'kind');

        if ($kind !== 'group') {
            $child = $this->normalizeRequirementChild($node);

            return [
                'key' => $this->arrayNullableString($node, 'key'),
                'name' => $this->arrayNullableString($node, 'name') ?? $child['name'],
                'kind' => 'group',
                'required_count' => 1,
                'modifiers' => $child['modifiers'],
                'children' => [$child],
            ];
        }

        $children = [];

        foreach ($node['children'] ?? [] as $childNode) {
            if (! is_array($childNode)) {
                continue;
            }

            $children[] = $this->normalizeRequirementChild($childNode);
        }

        return [
            'key' => $this->arrayNullableString($node, 'key'),
            'name' => $this->arrayNullableString($node, 'name'),
            'kind' => 'group',
            'required_count' => $this->arrayNullableInt($node, 'required_count'),
            'modifiers' => $this->normalizeModifiers($node['modifiers'] ?? []),
            'children' => $children,
        ];
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function normalizeRequirementChild(array $node): array
    {
        $normalized = [
            'key' => $this->arrayNullableString($node, 'key'),
            'kind' => $this->arrayNullableString($node, 'kind'),
            'uuid' => $this->arrayNullableString($node, 'uuid'),
            'name' => $this->arrayNullableString($node, 'name'),
            'required_count' => $this->arrayNullableInt($node, 'required_count'),
            'quantity' => $this->nullableNumeric($node['quantity'] ?? null),
            'quantity_scu' => $this->nullableNumeric($node['quantity_scu'] ?? null),
            'min_quality' => $this->arrayNullableInt($node, 'min_quality'),
            'modifiers' => $this->normalizeModifiers($node['modifiers'] ?? []),
        ];

        if (($normalized['kind'] ?? null) !== 'group') {
            return $normalized;
        }

        $children = [];

        foreach ($node['children'] ?? [] as $childNode) {
            if (! is_array($childNode)) {
                continue;
            }

            $children[] = $this->normalizeRequirementChild($childNode);
        }

        $normalized['children'] = $children;

        return $normalized;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeModifiers(mixed $modifiers): array
    {
        if (! is_array($modifiers)) {
            return [];
        }

        $normalized = [];

        foreach ($modifiers as $modifier) {
            if (! is_array($modifier)) {
                continue;
            }

            $propertyKey = $this->nullableString($modifier['property_key'] ?? $modifier['key'] ?? null);

            if ($propertyKey === null) {
                continue;
            }

            $normalized[] = [
                'property_key' => $propertyKey,
                'property_uuid' => $this->arrayNullableString($modifier, 'property_uuid'),
                'label' => $this->resolveModifierLabel($propertyKey, $modifier),
                'better_when' => $this->modifierBetterWhen($modifier),
                'quality_range' => [
                    'min' => $this->nullableNumeric(data_get($modifier, 'quality_range.min')),
                    'max' => $this->nullableNumeric(data_get($modifier, 'quality_range.max')),
                ],
                'modifier_range' => [
                    'at_min_quality' => $this->nullableNumeric(data_get($modifier, 'modifier_range.at_min_quality') ?? $modifier['value'] ?? null),
                    'at_max_quality' => $this->nullableNumeric(data_get($modifier, 'modifier_range.at_max_quality') ?? $modifier['value'] ?? null),
                ],
                'value_range_type' => $this->arrayNullableString($modifier, 'value_range_type'),
                'value_segments' => $this->normalizeValueSegments($modifier['value_segments'] ?? null),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $modifier
     */
    private function modifierBetterWhen(array $modifier): string
    {
        $explicitDirection = $this->arrayNullableString($modifier, 'better_when');

        if ($explicitDirection !== null) {
            return $explicitDirection;
        }

        $atMinQuality = $this->nullableNumeric(data_get($modifier, 'modifier_range.at_min_quality') ?? $modifier['value'] ?? null);
        $atMaxQuality = $this->nullableNumeric(data_get($modifier, 'modifier_range.at_max_quality') ?? $modifier['value'] ?? null);

        if ($atMinQuality === null || $atMaxQuality === null || $atMinQuality === $atMaxQuality) {
            return 'neutral';
        }

        return $atMaxQuality > $atMinQuality ? 'higher' : 'lower';
    }

    /**
     * @return array<int, array{quality_min: int, quality_max: int, modifier_at_start: float, modifier_at_end: float}>|null
     */
    private function normalizeValueSegments(mixed $segments): ?array
    {
        if (! is_array($segments) || $segments === []) {
            return null;
        }

        return array_values(array_map(function (array $segment): array {
            return [
                'quality_min' => (int) ($segment['quality_min'] ?? 0),
                'quality_max' => (int) ($segment['quality_max'] ?? 0),
                'modifier_at_start' => (float) ($segment['modifier_at_start'] ?? 1),
                'modifier_at_end' => (float) ($segment['modifier_at_end'] ?? 1),
            ];
        }, array_filter($segments, 'is_array')));
    }

    /**
     * @param  array<string, mixed>  $modifier
     */
    private function resolveModifierLabel(string $propertyKey, array $modifier): string
    {
        // Use the Name field from the raw data (e.g. "Integrity", "Quantum Speed", "Impact Force")
        $name = $this->arrayNullableString($modifier, 'name');
        if ($name !== null && $name !== '') {
            return $name;
        }

        // Pre 4.8 data
        $normalizedPropertyKey = str_replace(
            ['temperaturemax', 'temperaturemin', 'damagemitigation', 'health_maxhealth', 'quantum_fuelrequirement'],
            ['temperature max', 'temperature min', 'damage mitigation', 'max_health', 'quantum_fuel_requirement'],
            $propertyKey,
        );

        return Str::headline(str_replace(['.', '_', '-'], ' ', $normalizedPropertyKey));
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     */
    private function countRequirementChildren(array $children): int
    {
        $count = 0;

        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            if (($child['kind'] ?? null) === 'group') {
                $nestedChildren = $child['children'] ?? [];

                if (is_array($nestedChildren)) {
                    $count += $this->countRequirementChildren($nestedChildren);
                }

                continue;
            }

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @param  array<string, array{name: ?string, kind: ?string, resource_type_uuid: ?string, item_uuid: ?string, quantity_scu: int|float|null, quantity: int|float|null, link: ?string, web_url: ?string}>  $ingredients
     */
    private function collectIngredientsFromChildren(array $children, array &$ingredients): void
    {
        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            if (($child['kind'] ?? null) === 'group') {
                $nestedChildren = $child['children'] ?? [];

                if (is_array($nestedChildren)) {
                    $this->collectIngredientsFromChildren($nestedChildren, $ingredients);
                }

                continue;
            }

            $name = $this->nullableString($child['name'] ?? $child['key'] ?? null);
            $kind = $this->nullableString($child['kind'] ?? null);
            $uuid = $this->nullableString($child['uuid'] ?? null);
            $ingredientKey = $uuid ?? $name;

            if ($ingredientKey === null) {
                continue;
            }

            $isResource = $kind === 'resource';
            $isItem = $kind === 'item';

            $quantityScu = $isResource ? $this->nullableNumeric($child['quantity_scu'] ?? null) : null;
            $quantity = $isItem ? $this->nullableNumeric($child['quantity'] ?? null) : null;

            if (isset($ingredients[$ingredientKey]) && $quantityScu !== null) {
                $existing = $ingredients[$ingredientKey]['quantity_scu'];
                if ($existing !== null) {
                    $quantityScu = $existing + $quantityScu;
                }
            }

            if (isset($ingredients[$ingredientKey]) && $quantity !== null) {
                $existing = $ingredients[$ingredientKey]['quantity'];
                if ($existing !== null) {
                    $quantity = $existing + $quantity;
                }
            }

            $ingredients[$ingredientKey] ??= [
                'name' => $name,
                'kind' => $kind,
                'resource_type_uuid' => $isResource ? $uuid : null,
                'item_uuid' => $isItem ? $uuid : null,
                'quantity_scu' => null,
                'quantity' => null,
                'link' => null,
                'web_url' => null,
            ];

            if ($quantityScu !== null) {
                $ingredients[$ingredientKey]['quantity_scu'] = $quantityScu;
            }

            if ($quantity !== null) {
                $ingredients[$ingredientKey]['quantity'] = $quantity;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $modifier
     * @return array<string, mixed>
     */
    private function summaryProperty(array $modifier): array
    {
        return [
            'property_key' => $modifier['property_key'],
            'property_uuid' => $modifier['property_uuid'],
            'label' => $modifier['label'],
            'better_when' => $modifier['better_when'],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $summaryProperties
     * @param  array<int, array<string, mixed>>  $children
     */
    private function collectChildSummaryProperties(array &$summaryProperties, array $children): void
    {
        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            $this->collectSummaryProperties(
                $summaryProperties,
                is_array($child['modifiers'] ?? null) ? $child['modifiers'] : [],
            );

            $nestedChildren = $child['children'] ?? [];

            if (is_array($nestedChildren)) {
                $this->collectChildSummaryProperties($summaryProperties, $nestedChildren);
            }
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $summaryProperties
     * @param  array<int, array<string, mixed>>  $modifiers
     */
    private function collectSummaryProperties(array &$summaryProperties, array $modifiers): void
    {
        foreach ($modifiers as $modifier) {
            $propertyKey = $modifier['property_key'] ?? null;

            if (is_string($propertyKey) && $propertyKey !== '' && ! isset($summaryProperties[$propertyKey])) {
                $summaryProperties[$propertyKey] = $this->summaryProperty($modifier);
            }
        }
    }
}
