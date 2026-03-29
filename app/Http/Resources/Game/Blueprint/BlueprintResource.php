<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Blueprint;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'blueprint_output',
    title: 'Blueprint Output',
    description: 'Crafted output metadata for a blueprint.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'class', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'subtype', type: 'string', nullable: true),
        new OA\Property(property: 'grade', type: 'string', nullable: true),
        new OA\Property(property: 'item_web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_reward_pool',
    title: 'Blueprint Reward Pool',
    description: 'Reward pool that can grant access to a blueprint.',
    properties: [
        new OA\Property(property: 'key', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_availability',
    title: 'Blueprint Availability',
    description: 'Availability metadata for a blueprint.',
    properties: [
        new OA\Property(property: 'default', type: 'boolean'),
        new OA\Property(
            property: 'reward_pools',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_reward_pool')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_modifier_quality_range',
    title: 'Blueprint Modifier Quality Range',
    properties: [
        new OA\Property(property: 'min', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'max', type: 'number', format: 'float', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_modifier_range',
    title: 'Blueprint Modifier Range',
    properties: [
        new OA\Property(property: 'at_min_quality', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'at_max_quality', type: 'number', format: 'float', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_modifier',
    title: 'Blueprint Modifier',
    description: 'Modifier interpolation metadata used for blueprint quality effects.',
    properties: [
        new OA\Property(property: 'property_key', type: 'string'),
        new OA\Property(property: 'property_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'label', type: 'string'),
        new OA\Property(property: 'better_when', type: 'string', enum: ['higher', 'lower', 'neutral'], nullable: true),
        new OA\Property(property: 'quality_range', ref: '#/components/schemas/blueprint_modifier_quality_range'),
        new OA\Property(property: 'modifier_range', ref: '#/components/schemas/blueprint_modifier_range'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_summary_property',
    title: 'Blueprint Summary Property',
    description: 'Aggregated modifier property shown in blueprint output summaries.',
    properties: [
        new OA\Property(property: 'property_key', type: 'string'),
        new OA\Property(property: 'property_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'label', type: 'string'),
        new OA\Property(property: 'better_when', type: 'string', enum: ['higher', 'lower', 'neutral'], nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_requirement_child',
    title: 'Blueprint Requirement Child',
    description: 'Normalized blueprint requirement child entry. Children can represent groups, resources, or discrete items.',
    properties: [
        new OA\Property(property: 'key', type: 'string', nullable: true),
        new OA\Property(property: 'kind', type: 'string', enum: ['group', 'resource', 'item'], nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'required_count', type: 'integer', nullable: true),
        new OA\Property(property: 'quantity', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'quantity_scu', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'min_quality', type: 'integer', nullable: true),
        new OA\Property(
            property: 'modifiers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_modifier')
        ),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_requirement_child'),
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_requirement_group',
    title: 'Blueprint Requirement Group',
    description: 'Normalized requirement group derived from the first blueprint tier.',
    properties: [
        new OA\Property(property: 'key', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'kind', type: 'string', enum: ['group']),
        new OA\Property(property: 'required_count', type: 'integer', nullable: true),
        new OA\Property(
            property: 'modifiers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_modifier')
        ),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_requirement_child')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_requirement_node',
    title: 'Blueprint Requirement Node',
    description: 'Raw recursive requirement node returned under tiers[].requirements.',
    properties: [
        new OA\Property(property: 'kind', type: 'string', enum: ['root', 'group', 'resource', 'item'], nullable: true),
        new OA\Property(property: 'key', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'required_count', type: 'integer', nullable: true),
        new OA\Property(property: 'quantity', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'quantity_scu', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'min_quality', type: 'integer', nullable: true),
        new OA\Property(
            property: 'modifiers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_modifier'),
            nullable: true
        ),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_requirement_node'),
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_tier',
    title: 'Blueprint Tier',
    description: 'Raw blueprint tier as imported from the source payload.',
    properties: [
        new OA\Property(property: 'tier_index', type: 'integer', nullable: true),
        new OA\Property(property: 'craft_time_seconds', type: 'integer', nullable: true),
        new OA\Property(property: 'requirements', ref: '#/components/schemas/blueprint_requirement_node', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_ingredient',
    title: 'Blueprint Ingredient',
    description: 'Condensed ingredient summary used by blueprint list and detail responses.',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'resource_type_uuid', type: 'string', format: 'uuid', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint',
    title: 'Blueprint',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'key', type: 'string'),
        new OA\Property(property: 'category_uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'output_item_uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'output_name', type: 'string', nullable: true),
        new OA\Property(property: 'output_class', type: 'string', nullable: true),
        new OA\Property(property: 'craft_time_seconds', type: 'integer', nullable: true),
        new OA\Property(property: 'is_available_by_default', type: 'boolean'),
        new OA\Property(property: 'game_version', type: 'string', nullable: true),
        new OA\Property(property: 'ingredient_count', type: 'integer'),
        new OA\Property(
            property: 'ingredients',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_ingredient')
        ),
        new OA\Property(property: 'output', ref: '#/components/schemas/blueprint_output'),
        new OA\Property(
            property: 'availability',
            ref: '#/components/schemas/blueprint_availability',
            description: 'Only included on blueprint detail responses.'
        ),
        new OA\Property(
            property: 'requirement_groups',
            description: 'Only included on blueprint detail responses.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_requirement_group')
        ),
        new OA\Property(
            property: 'summary_properties',
            description: 'Only included on blueprint detail responses.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_summary_property')
        ),
        new OA\Property(
            property: 'tiers',
            description: 'Only included on blueprint detail responses.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_tier')
        ),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
        new OA\Property(property: 'output_item_web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
class BlueprintResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $payload = $this->rawPayload();

        return [
            'uuid' => $this->blueprint->uuid,
            'key' => $this->key,
            'category_uuid' => $this->category_uuid,
            'output_item_uuid' => $this->output_item_uuid,
            'output_name' => $this->output_name,
            'output_class' => $this->output_class,
            'craft_time_seconds' => $this->craft_time_seconds,
            'is_available_by_default' => $this->is_available_by_default,
            'game_version' => $this->gameVersion?->code,
            'ingredient_count' => $this->ingredientCount($payload),
            'ingredients' => $this->ingredients($payload),
            'output' => $this->outputPayload($request, $payload),
            'web_url' => $this->webUrl($request),
            'output_item_web_url' => $this->whenNotNull($this->outputItemWebUrl($request)),
            $this->mergeWhen($this->shouldIncludeDetailFields($request), [
                'availability' => $this->availabilityPayload($payload),
                'requirement_groups' => $this->requirementGroups($payload),
                'summary_properties' => $this->summaryProperties($payload),
            ]),
            'tiers' => $this->when(
                $this->shouldIncludeDetailFields($request),
                fn (): array => $this->rawTiers(),
            ),
            'link' => $this->urlWithVersion(
                route('blueprints.show', ['blueprint' => $this->blueprint->uuid]),
                $request,
            ),
        ];
    }

    private function shouldIncludeDetailFields(Request $request): bool
    {
        return $request->routeIs('blueprints.show');
    }

    /**
     * @return array<string, mixed>
     */
    private function rawPayload(): array
    {
        $payload = $this->resource->data;

        if ($payload instanceof Collection) {
            return $payload->toArray();
        }

        return is_array($payload) ? $payload : [];
    }

    private function rawTiers(): array
    {
        $tiers = data_get($this->rawPayload(), 'tiers');

        return is_array($tiers) ? $tiers : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function outputPayload(Request $request, array $payload): array
    {
        $output = data_get($payload, 'output');
        $output = is_array($output) ? $output : [];

        $uuid = $this->stringValue($output['uuid'] ?? null) ?? $this->stringValue($this->output_item_uuid);

        return [
            'uuid' => $uuid,
            'name' => $this->stringValue($output['name'] ?? null) ?? $this->stringValue($this->output_name),
            'class' => $this->stringValue($output['class'] ?? null) ?? $this->stringValue($this->output_class),
            'type' => $this->stringValue($output['type'] ?? null),
            'subtype' => $this->stringValue($output['subtype'] ?? null),
            'grade' => $this->stringValue($output['grade'] ?? null),
            'item_web_url' => $uuid !== null && Str::isUuid($uuid)
                ? $this->urlWithVersion(route('web.items.show', ['item' => $uuid]), $request)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function availabilityPayload(array $payload): array
    {
        $availability = data_get($payload, 'availability');
        $availability = is_array($availability) ? $availability : [];

        $rewardPools = data_get($availability, 'reward_pools', []);
        $rewardPools = is_array($rewardPools) ? $rewardPools : [];

        $rewardPoolKey = $this->stringValue($availability['reward_pool'] ?? null);

        if ($rewardPools === [] && $rewardPoolKey !== null) {
            $rewardPools = [
                ['key' => $rewardPoolKey],
            ];
        }

        return [
            'default' => (bool) ($availability['default'] ?? $this->is_available_by_default),
            'reward_pools' => array_values(array_filter(array_map(
                fn (mixed $rewardPool): ?array => is_array($rewardPool)
                    ? array_filter([
                        'key' => $this->stringValue($rewardPool['key'] ?? null),
                        'uuid' => $this->stringValue($rewardPool['uuid'] ?? null),
                    ], static fn (mixed $value): bool => $value !== null)
                    : null,
                $rewardPools,
            ))),
        ];
    }

    private function ingredientCount(array $payload): int
    {
        $ingredientCount = 0;

        foreach ($this->requirementGroups($payload) as $group) {
            $ingredientCount += $this->countRequirementChildren(
                is_array($group['children'] ?? null) ? $group['children'] : [],
            );
        }

        if ($ingredientCount > 0) {
            return $ingredientCount;
        }

        $ingredientResourceTypeUuids = $this->ingredient_resource_type_uuids ?? [];

        return is_array($ingredientResourceTypeUuids) ? count($ingredientResourceTypeUuids) : 0;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array{name: ?string, resource_type_uuid: ?string}>
     */
    private function ingredients(array $payload): array
    {
        $ingredients = [];

        foreach ($this->requirementGroups($payload) as $group) {
            $this->collectIngredients(
                is_array($group['children'] ?? null) ? $group['children'] : [],
                $ingredients,
            );
        }

        $ingredientResourceTypeUuids = $this->ingredient_resource_type_uuids ?? [];

        if (is_array($ingredientResourceTypeUuids)) {
            foreach ($ingredientResourceTypeUuids as $ingredientResourceTypeUuid) {
                $resourceTypeUuid = $this->stringValue($ingredientResourceTypeUuid);

                if ($resourceTypeUuid === null || array_key_exists($resourceTypeUuid, $ingredients)) {
                    continue;
                }

                $ingredients[$resourceTypeUuid] = [
                    'name' => null,
                    'resource_type_uuid' => $resourceTypeUuid,
                ];
            }
        }

        return array_values($ingredients);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function requirementGroups(array $payload): array
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
    private function summaryProperties(array $payload): array
    {
        $summaryProperties = [];

        foreach ($this->requirementGroups($payload) as $group) {
            $this->collectSummaryProperties($summaryProperties, $group['modifiers']);
            $this->collectChildSummaryProperties($summaryProperties, $group['children']);
        }

        return array_values($summaryProperties);
    }

    private function webUrl(Request $request): string
    {
        return $this->urlWithVersion(route('web.blueprints.show', ['blueprint' => $this->blueprint->uuid]), $request);
    }

    private function outputItemWebUrl(Request $request): ?string
    {
        $outputItemUuid = $this->stringValue($this->output_item_uuid);

        if ($outputItemUuid === null || ! Str::isUuid($outputItemUuid)) {
            return null;
        }

        return $this->urlWithVersion(route('web.items.show', ['item' => $outputItemUuid]), $request);
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
        $kind = $this->stringValue($node['kind'] ?? null);

        if ($kind !== 'group') {
            $child = $this->normalizeRequirementChild($node);

            return [
                'key' => $this->stringValue($node['key'] ?? null),
                'name' => $this->stringValue($node['name'] ?? null) ?? $child['name'],
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
            'key' => $this->stringValue($node['key'] ?? null),
            'name' => $this->stringValue($node['name'] ?? null),
            'kind' => 'group',
            'required_count' => $this->integerValue($node['required_count'] ?? null),
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
            'key' => $this->stringValue($node['key'] ?? null),
            'kind' => $this->stringValue($node['kind'] ?? null),
            'uuid' => $this->stringValue($node['uuid'] ?? null),
            'name' => $this->stringValue($node['name'] ?? null),
            'required_count' => $this->integerValue($node['required_count'] ?? null),
            'quantity' => $this->numericValue($node['quantity'] ?? null),
            'quantity_scu' => $this->numericValue($node['quantity_scu'] ?? null),
            'min_quality' => $this->integerValue($node['min_quality'] ?? null),
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
     * @param  array<string, array{name: ?string, resource_type_uuid: ?string}>  $ingredients
     */
    private function collectIngredients(array $children, array &$ingredients): void
    {
        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            if (($child['kind'] ?? null) === 'group') {
                $nestedChildren = $child['children'] ?? [];

                if (is_array($nestedChildren)) {
                    $this->collectIngredients($nestedChildren, $ingredients);
                }

                continue;
            }

            $name = $this->stringValue($child['name'] ?? $child['key'] ?? null);
            $resourceTypeUuid = ($child['kind'] ?? null) === 'resource'
                ? $this->stringValue($child['uuid'] ?? null)
                : null;
            $ingredientKey = $resourceTypeUuid ?? $name;

            if ($ingredientKey === null) {
                continue;
            }

            $ingredients[$ingredientKey] ??= [
                'name' => $name,
                'resource_type_uuid' => $resourceTypeUuid,
            ];
        }
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

            $propertyKey = $this->stringValue($modifier['property_key'] ?? $modifier['key'] ?? null);

            if ($propertyKey === null) {
                continue;
            }

            $normalized[] = [
                'property_key' => $propertyKey,
                'property_uuid' => $this->stringValue($modifier['property_uuid'] ?? null),
                'label' => $this->modifierLabel($propertyKey),
                'better_when' => $this->modifierBetterWhen($modifier),
                'quality_range' => [
                    'min' => $this->numericValue(data_get($modifier, 'quality_range.min')),
                    'max' => $this->numericValue(data_get($modifier, 'quality_range.max')),
                ],
                'modifier_range' => [
                    'at_min_quality' => $this->numericValue(data_get($modifier, 'modifier_range.at_min_quality') ?? $modifier['value'] ?? null),
                    'at_max_quality' => $this->numericValue(data_get($modifier, 'modifier_range.at_max_quality') ?? $modifier['value'] ?? null),
                ],
            ];
        }

        return $normalized;
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

    /**
     * @param  array<string, mixed>  $modifier
     */
    private function modifierBetterWhen(array $modifier): string
    {
        $explicitDirection = $this->stringValue($modifier['better_when'] ?? null);

        if ($explicitDirection !== null) {
            return $explicitDirection;
        }

        $atMinQuality = $this->numericValue(data_get($modifier, 'modifier_range.at_min_quality') ?? $modifier['value'] ?? null);
        $atMaxQuality = $this->numericValue(data_get($modifier, 'modifier_range.at_max_quality') ?? $modifier['value'] ?? null);

        if ($atMinQuality === null || $atMaxQuality === null || $atMinQuality === $atMaxQuality) {
            return 'neutral';
        }

        return $atMaxQuality > $atMinQuality ? 'higher' : 'lower';
    }

    private function modifierLabel(string $propertyKey): string
    {
        $normalizedPropertyKey = str_replace(
            ['temperaturemax', 'temperaturemin', 'damagemitigation'],
            ['temperature max', 'temperature min', 'damage mitigation'],
            $propertyKey,
        );

        return Str::headline(str_replace(['.', '_', '-'], ' ', $normalizedPropertyKey));
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function integerValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function numericValue(mixed $value): int|float|null
    {
        if (! is_numeric($value)) {
            return null;
        }

        $numericValue = $value + 0;

        if (is_float($numericValue) && floor($numericValue) === $numericValue) {
            return (int) $numericValue;
        }

        return $numericValue;
    }
}
