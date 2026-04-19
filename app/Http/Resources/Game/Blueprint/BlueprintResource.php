<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Blueprint;

use App\Http\Resources\AbstractBaseResource;
use App\Support\Formatting\FormatDuration;
use App\Support\Formatting\FormatMissionTitle;
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
        new OA\Property(property: 'quantity_scu', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_dismantle_return_summary',
    title: 'Blueprint Dismantle Return Summary',
    description: 'Lightweight dismantle return entry used by blueprint list responses.',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'resource_type_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'quantity_scu', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_dismantle',
    title: 'Blueprint Dismantle',
    description: 'Dismantle metadata for a blueprint. Only included on blueprint detail responses.',
    properties: [
        new OA\Property(property: 'time_seconds', type: 'integer', nullable: true),
        new OA\Property(property: 'time_label', type: 'string', nullable: true),
        new OA\Property(property: 'efficiency', type: 'number', format: 'float', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_unlocking_mission',
    title: 'Blueprint Unlocking Mission',
    description: 'A mission that can unlock this blueprint as a reward.',
    properties: [
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'debug_name', type: 'string', nullable: true),
        new OA\Property(property: 'reward_scope', type: 'string', nullable: true),
        new OA\Property(property: 'chance', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
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
        new OA\Property(property: 'craft_time_label', type: 'string', nullable: true),
        new OA\Property(property: 'is_available_by_default', type: 'boolean'),
        new OA\Property(property: 'game_version', type: 'string', nullable: true),
        new OA\Property(property: 'ingredient_count', type: 'integer'),
        new OA\Property(property: 'unlocking_missions_count', description: 'Number of missions that can unlock this blueprint.', type: 'integer'),
        new OA\Property(
            property: 'ingredients',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_ingredient')
        ),
        new OA\Property(
            property: 'dismantle_returns',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_dismantle_return_summary')
        ),
        new OA\Property(property: 'output', ref: '#/components/schemas/blueprint_output'),
        new OA\Property(
            property: 'dismantle',
            ref: '#/components/schemas/blueprint_dismantle',
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
            property: 'unlocking_missions',
            description: 'Only included on blueprint detail responses.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_unlocking_mission')
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
    private ?array $normalizedPayload = null;

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
            'craft_time_label' => FormatDuration::fromSeconds($this->craft_time_seconds),
            'is_available_by_default' => $this->is_available_by_default,
            'game_version' => $this->gameVersion?->code,
            'ingredient_count' => $this->ingredientCount($payload),
            'unlocking_missions_count' => (int) ($this->resource->missions_count ?? 0),
            'ingredients' => $this->ingredients($payload, $request),
            'dismantle_returns' => $this->dismantleReturnsList($request),
            'output' => $this->outputPayload($request, $payload),
            'web_url' => $this->webUrl($request),
            'output_item_web_url' => $this->whenNotNull($this->outputItemWebUrl($request)),
            $this->mergeWhen($this->shouldIncludeDetailFields($request), [
                'dismantle' => $this->dismantlePayload($payload),
                'requirement_groups' => $this->requirementGroups($payload),
                'summary_properties' => $this->summaryProperties($payload),
                'unlocking_missions' => $this->unlockingMissions(),
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
     * @return array<int, array{title: ?string, debug_name: ?string, reward_scope: ?string, chance: int|float|null, web_url: ?string}>
     */
    private function unlockingMissions(): array
    {
        $missions = $this->loadedRelation('missions');

        return $missions->map(fn ($mission): array => [
            'title' => FormatMissionTitle::format(
                $this->nullableString($mission->title),
                $this->nullableString($mission->debug_name),
            ),
            'debug_name' => $this->nullableString($mission->debug_name),
            'reward_scope' => $this->nullableString($mission->reward_scope),
            'chance' => $this->nullableNumeric($mission->blueprint_drop_chance ?? null),
            'web_url' => $mission->relationLoaded('mission') && $mission->mission !== null
                ? $this->urlWithVersion(route('web.missions.show', ['mission' => $mission->mission->uuid]), request())
                : null,
        ])->sortBy('title', SORT_STRING | SORT_FLAG_CASE)->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function rawPayload(): array
    {
        if ($this->normalizedPayload !== null) {
            return $this->normalizedPayload;
        }

        $payload = $this->resource->data;

        if ($payload instanceof Collection) {
            $payload = $payload->toArray();
        }

        $payload = is_array($payload) ? $payload : [];

        $this->normalizedPayload = $this->normalizePayloadKeys($payload);

        return $this->normalizedPayload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayloadKeys(array $payload): array
    {
        $normalized = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = is_string($key) ? $this->normalizeKey($key) : $key;
            $normalized[$normalizedKey] = is_array($value) ? $this->normalizePayloadKeys($value) : $value;
        }

        return $normalized;
    }

    private function normalizeKey(string $key): string
    {
        $key = preg_replace_callback(
            '/[A-Z]{2,}/',
            static fn (array $m): string => ucfirst(strtolower($m[0])),
            $key,
        );

        return Str::snake($key);
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
        $output = data_get($payload, 'Output') ?? data_get($payload, 'output');
        $output = is_array($output) ? $output : [];

        $uuid = $this->arrayNullableString($output, 'UUID') ?? $this->arrayNullableString($output, 'uuid') ?? $this->nullableString($this->output_item_uuid);

        return [
            'uuid' => $uuid,
            'name' => $this->arrayNullableString($output, 'Name') ?? $this->arrayNullableString($output, 'name') ?? $this->nullableString($this->output_name),
            'class' => $this->arrayNullableString($output, 'Class') ?? $this->arrayNullableString($output, 'class') ?? $this->nullableString($this->output_class),
            'type' => $this->arrayNullableString($output, 'Type') ?? $this->arrayNullableString($output, 'type'),
            'subtype' => $this->arrayNullableString($output, 'Subtype') ?? $this->arrayNullableString($output, 'subtype'),
            'grade' => $this->arrayNullableString($output, 'Grade') ?? $this->arrayNullableString($output, 'grade'),
            'item_web_url' => $uuid !== null && Str::isUuid($uuid)
                ? $this->urlWithVersion(route('web.items.show', ['item' => $uuid]), $request)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function dismantlePayload(array $payload): array
    {
        $dismantle = data_get($payload, 'dismantle');
        $dismantle = is_array($dismantle) ? $dismantle : [];

        $timeSeconds = $this->arrayNullableInt($dismantle, 'time_seconds');

        return [
            'time_seconds' => $timeSeconds,
            'time_label' => FormatDuration::fromSeconds($timeSeconds),
            'efficiency' => $this->nullableNumeric($dismantle['efficiency'] ?? null),
        ];
    }

    /**
     * @return array<int, array{name: ?string, resource_type_uuid: ?string, quantity_scu: int|float|null, link: ?string, web_url: ?string}>
     */
    private function dismantleReturnsList(Request $request): array
    {
        return $this->loadedRelation('dismantleReturns')
            ->map(fn ($commodity): array => $this->mapDismantleReturn($commodity, $request))
            ->values()
            ->all();
    }

    private function mapDismantleReturn(mixed $commodity, Request $request): array
    {
        return [
            'name' => $this->nullableString($commodity->name),
            'resource_type_uuid' => $this->nullableString($commodity->uuid),
            'quantity_scu' => $this->nullableNumeric($commodity->pivot->quantity_scu ?? null),
            ...$this->commodityLinks($commodity->uuid, $request),
        ];
    }

    private function commodityLinks(string $uuid, Request $request): array
    {
        return [
            'link' => $this->urlWithVersion(
                route('commodities.show', ['commodity' => $uuid]),
                $request,
            ),
            'web_url' => $this->urlWithVersion(
                route('web.commodities.show', ['identifier' => $uuid]),
                $request,
            ),
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

        $ingredientResourceTypeUuids = $this->ingredients->pluck('uuid')->all();

        return count($ingredientResourceTypeUuids);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array{name: ?string, resource_type_uuid: ?string, quantity_scu: int|float|null, link: ?string, web_url: ?string}>
     */
    private function ingredients(array $payload, Request $request): array
    {
        $ingredients = [];

        foreach ($this->requirementGroups($payload) as $group) {
            $this->collectIngredients(
                is_array($group['children'] ?? null) ? $group['children'] : [],
                $ingredients,
            );
        }

        $loadedIngredients = $this->loadedRelation('ingredients')->keyBy('uuid');

        $ingredientResourceTypeUuids = $this->ingredients->pluck('uuid')->all();

        if (is_array($ingredientResourceTypeUuids)) {
            foreach ($ingredientResourceTypeUuids as $ingredientResourceTypeUuid) {
                $resourceTypeUuid = $this->nullableString($ingredientResourceTypeUuid);

                if ($resourceTypeUuid === null || array_key_exists($resourceTypeUuid, $ingredients)) {
                    continue;
                }

                $commodity = $loadedIngredients->get($resourceTypeUuid);

                $ingredients[$resourceTypeUuid] = [
                    'name' => $commodity ? $this->nullableString($commodity->name) : null,
                    'resource_type_uuid' => $resourceTypeUuid,
                    'quantity_scu' => null,
                    'link' => null,
                    'web_url' => null,
                ];
            }
        }

        foreach ($ingredients as $key => $ingredient) {
            if ($ingredient['name'] === null && $ingredient['resource_type_uuid'] !== null) {
                $commodity = $loadedIngredients->get($ingredient['resource_type_uuid']);
                if ($commodity !== null) {
                    $ingredients[$key]['name'] = $this->nullableString($commodity->name);
                }
            }
        }

        return array_values(array_map(function (array $ingredient) use ($request): array {
            $uuid = $ingredient['resource_type_uuid'];

            if ($uuid !== null && Str::isUuid($uuid)) {
                $ingredient = [...$ingredient, ...$this->commodityLinks($uuid, $request)];
            }

            return $ingredient;
        }, $ingredients));
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
        $outputItemUuid = $this->nullableString($this->output_item_uuid);

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
     * @param  array<string, array{name: ?string, resource_type_uuid: ?string, quantity_scu: int|float|null, link: ?string, web_url: ?string}>  $ingredients
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

            $name = $this->nullableString($child['name'] ?? $child['key'] ?? null);
            $resourceTypeUuid = ($child['kind'] ?? null) === 'resource'
                ? $this->nullableString($child['uuid'] ?? null)
                : null;
            $ingredientKey = $resourceTypeUuid ?? $name;

            if ($ingredientKey === null) {
                continue;
            }

            $quantityScu = $this->nullableNumeric($child['quantity_scu'] ?? null);

            if (isset($ingredients[$ingredientKey]) && $quantityScu !== null) {
                $existing = $ingredients[$ingredientKey]['quantity_scu'];
                if ($existing !== null) {
                    $quantityScu = $existing + $quantityScu;
                }
            }

            $ingredients[$ingredientKey] ??= [
                'name' => $name,
                'resource_type_uuid' => $resourceTypeUuid,
                'quantity_scu' => null,
                'link' => null,
                'web_url' => null,
            ];

            if ($quantityScu !== null) {
                $ingredients[$ingredientKey]['quantity_scu'] = $quantityScu;
            }
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

            $propertyKey = $this->nullableString($modifier['property_key'] ?? $modifier['key'] ?? null);

            if ($propertyKey === null) {
                continue;
            }

            $normalized[] = [
                'property_key' => $propertyKey,
                'property_uuid' => $this->arrayNullableString($modifier, 'property_uuid'),
                'label' => $this->modifierLabel($propertyKey),
                'better_when' => $this->modifierBetterWhen($modifier),
                'quality_range' => [
                    'min' => $this->nullableNumeric(data_get($modifier, 'quality_range.min')),
                    'max' => $this->nullableNumeric(data_get($modifier, 'quality_range.max')),
                ],
                'modifier_range' => [
                    'at_min_quality' => $this->nullableNumeric(data_get($modifier, 'modifier_range.at_min_quality') ?? $modifier['value'] ?? null),
                    'at_max_quality' => $this->nullableNumeric(data_get($modifier, 'modifier_range.at_max_quality') ?? $modifier['value'] ?? null),
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

    private function loadedRelation(string $relation): Collection
    {
        return $this->resource->relationLoaded($relation)
            ? $this->resource->$relation
            : collect();
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
}
