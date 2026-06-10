<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Blueprint;

use App\Http\Resources\AbstractBaseResource;
use App\Support\Filters\ItemFilterLabel;
use App\Support\Formatting\FormatDuration;
use App\Support\Formatting\FormatMissionText;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'blueprint_output',
    title: 'Blueprint Output',
    description: 'Crafted output metadata for a blueprint.',
    properties: [
        new OA\Property(property: 'uuid', description: 'UUID of the crafted item', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the crafted item', type: 'string', nullable: true),
        new OA\Property(property: 'class', description: 'Internal class identifier of the crafted item', type: 'string', nullable: true),
        new OA\Property(property: 'type', description: 'Type category of the crafted item (e.g. WeaponPersonal, Char_Armor_Torso)', type: 'string', nullable: true),
        new OA\Property(property: 'type_label', description: 'Human-readable label for the type', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', description: 'Sub-type classification of the crafted item', type: 'string', nullable: true),
        new OA\Property(property: 'subtype', description: 'Deprecated: Use sub_type.', type: 'string', nullable: true, deprecated: true),
        new OA\Property(property: 'grade', description: 'Grade or quality tier of the crafted item', type: 'string', nullable: true),
        new OA\Property(property: 'item_web_url', description: 'Web URL for the crafted item detail page', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_modifier_quality_range',
    title: 'Blueprint Modifier Quality Range',
    properties: [
        new OA\Property(property: 'min', description: 'Minimum quality value', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'max', description: 'Maximum quality value', type: 'number', format: 'float', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_modifier_range',
    title: 'Blueprint Modifier Range',
    properties: [
        new OA\Property(property: 'at_min_quality', description: 'Modifier value at minimum quality', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'at_max_quality', description: 'Modifier value at maximum quality', type: 'number', format: 'float', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_modifier',
    title: 'Blueprint Modifier',
    description: 'Modifier interpolation metadata used for blueprint quality effects.',
    properties: [
        new OA\Property(property: 'property_key', description: 'Internal key identifying the modifier property', type: 'string'),
        new OA\Property(property: 'property_uuid', description: 'UUID of the property definition, if available', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'label', description: 'Human-readable label for the modifier', type: 'string'),
        new OA\Property(property: 'better_when', description: 'Indicates whether a higher or lower value is desirable', type: 'string', enum: ['higher', 'lower', 'neutral'], nullable: true),
        new OA\Property(property: 'quality_range', ref: '#/components/schemas/blueprint_modifier_quality_range'),
        new OA\Property(property: 'modifier_range', ref: '#/components/schemas/blueprint_modifier_range'),
        new OA\Property(property: 'value_range_type', description: 'Interpolation type for the modifier value range. When present, value_segments should be used for interpolation instead of the simple quality_range/modifier_range pair.', type: 'string', enum: ['linear'], nullable: true),
        new OA\Property(
            property: 'value_segments',
            description: 'Multi-step interpolation segments. Each segment defines its own quality range and modifier start/end values. When present, use these for interpolation instead of quality_range/modifier_range.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'quality_min', description: 'Start quality for this segment', type: 'integer'),
                    new OA\Property(property: 'quality_max', description: 'End quality for this segment', type: 'integer'),
                    new OA\Property(property: 'modifier_at_start', description: 'Modifier value at quality_min', type: 'number', format: 'float'),
                    new OA\Property(property: 'modifier_at_end', description: 'Modifier value at quality_max', type: 'number', format: 'float'),
                ],
                type: 'object',
            ),
            nullable: true,
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_summary_property',
    title: 'Blueprint Summary Property',
    description: 'Aggregated modifier property shown in blueprint output summaries.',
    properties: [
        new OA\Property(property: 'property_key', description: 'Internal key identifying the property', type: 'string'),
        new OA\Property(property: 'property_uuid', description: 'UUID of the property definition, if available', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'label', description: 'Human-readable label for the property', type: 'string'),
        new OA\Property(property: 'better_when', description: 'Indicates whether a higher or lower value is desirable', type: 'string', enum: ['higher', 'lower', 'neutral'], nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_requirement_child',
    title: 'Blueprint Requirement Child',
    description: 'Normalized blueprint requirement child entry. Children can represent groups, resources, or discrete items.',
    properties: [
        new OA\Property(property: 'key', description: 'Internal key of the requirement child', type: 'string', nullable: true),
        new OA\Property(property: 'kind', description: 'Type of requirement entry', type: 'string', enum: ['group', 'resource', 'item'], nullable: true),
        new OA\Property(property: 'uuid', description: 'UUID of the required resource type or item', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the required resource or item', type: 'string', nullable: true),
        new OA\Property(property: 'required_count', description: 'Number of this child required within its group', type: 'integer', nullable: true),
        new OA\Property(property: 'quantity', description: 'Discrete item count required', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'quantity_scu', description: 'Quantity in Standard Cargo Units (for resources)', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'min_quality', description: 'Minimum quality tier required', type: 'integer', nullable: true),
        new OA\Property(
            property: 'modifiers',
            description: 'Quality-dependent modifier effects for this child',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_modifier')
        ),
        new OA\Property(
            property: 'children',
            description: 'Nested children when kind is group',
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
        new OA\Property(property: 'key', description: 'Internal key of the requirement group', type: 'string', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the requirement group', type: 'string', nullable: true),
        new OA\Property(property: 'kind', description: 'Always "group"', type: 'string', enum: ['group']),
        new OA\Property(property: 'required_count', description: 'Number of children that must be fulfilled within this group', type: 'integer', nullable: true),
        new OA\Property(
            property: 'modifiers',
            description: 'Quality-dependent modifier effects applied at the group level',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_modifier')
        ),
        new OA\Property(
            property: 'children',
            description: 'Individual resources or items within this group',
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
        new OA\Property(property: 'kind', description: 'Node type in the requirement tree', type: 'string', enum: ['root', 'group', 'resource', 'item'], nullable: true),
        new OA\Property(property: 'key', description: 'Internal key of the requirement node', type: 'string', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the requirement node', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', description: 'UUID of the required resource type or item', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'required_count', description: 'Number of children that must be fulfilled', type: 'integer', nullable: true),
        new OA\Property(property: 'quantity', description: 'Discrete item count required', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'quantity_scu', description: 'Quantity in Standard Cargo Units (for resources)', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'min_quality', description: 'Minimum quality tier required', type: 'integer', nullable: true),
        new OA\Property(
            property: 'modifiers',
            description: 'Quality-dependent modifier effects',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_modifier'),
            nullable: true
        ),
        new OA\Property(
            property: 'children',
            description: 'Nested child nodes in the requirement tree',
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
        new OA\Property(property: 'tier_index', description: 'Zero-based index of this crafting tier', type: 'integer', nullable: true),
        new OA\Property(property: 'craft_time_seconds', description: 'Crafting duration in seconds for this tier', type: 'integer', nullable: true),
        new OA\Property(property: 'requirements', ref: '#/components/schemas/blueprint_requirement_node', description: 'Recursive requirement tree for this tier', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_ingredient',
    title: 'Blueprint Ingredient',
    description: 'Condensed ingredient summary used by blueprint list and detail responses.',
    properties: [
        new OA\Property(property: 'name', description: 'Display name of the ingredient', type: 'string', nullable: true),
        new OA\Property(property: 'kind', description: 'Ingredient kind', type: 'string', enum: ['resource', 'item'], nullable: true),
        new OA\Property(property: 'resource_type_uuid', description: 'UUID of the ingredient resource type (see GET /api/commodities)', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'item_uuid', description: 'UUID of the ingredient item (see GET /api/items)', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'quantity_scu', description: 'Quantity in Standard Cargo Units (for resources)', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'quantity', description: 'Discrete count (for items)', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'link', description: 'API URL for the ingredient', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', description: 'Web URL for the ingredient detail page', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_dismantle_return_summary',
    title: 'Blueprint Dismantle Return Summary',
    description: 'Lightweight dismantle return entry used by blueprint list responses.',
    properties: [
        new OA\Property(property: 'name', description: 'Display name of the returned resource', type: 'string', nullable: true),
        new OA\Property(property: 'resource_type_uuid', description: 'UUID of the returned resource type (see GET /api/commodities)', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'quantity_scu', description: 'Quantity returned in Standard Cargo Units', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'link', description: 'API URL for the returned resource', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', description: 'Web URL for the returned resource detail page', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_dismantle',
    title: 'Blueprint Dismantle',
    description: 'Dismantle metadata for a blueprint. Only included on blueprint detail responses.',
    properties: [
        new OA\Property(property: 'time_seconds', description: 'Dismantle duration in seconds', type: 'integer', nullable: true),
        new OA\Property(property: 'time_label', description: 'Human-readable dismantle duration', type: 'string', nullable: true),
        new OA\Property(property: 'efficiency', description: 'Dismantle efficiency ratio', type: 'number', format: 'float', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_unlocking_mission',
    title: 'Blueprint Unlocking Mission',
    description: 'A mission that can unlock this blueprint as a reward.',
    properties: [
        new OA\Property(property: 'title', description: 'Formatted mission title', type: 'string', nullable: true),
        new OA\Property(property: 'debug_name', description: 'Internal debug name of the mission', type: 'string', nullable: true),
        new OA\Property(property: 'reward_scope', description: 'Scope of the blueprint reward', type: 'string', nullable: true),
        new OA\Property(property: 'chance', description: 'Drop chance as a decimal (e.g. 0.5 for 50%)', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'web_url', description: 'Web URL for the mission detail page', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_unlocking_missions_grouped_entry',
    title: 'Blueprint Unlocking Missions Grouped Entry',
    description: 'A grouped mission entry within unlocking_missions_grouped.',
    properties: [
        new OA\Property(property: 'title', description: 'Mission title', type: 'string'),
        new OA\Property(property: 'reward_scope', description: 'Scope of the blueprint reward', type: 'string', nullable: true),
        new OA\Property(property: 'count', description: 'Number of occurrences of this mission', type: 'integer'),
        new OA\Property(property: 'web_url', description: 'Web URL for the mission detail page', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_unlocking_missions_grouped',
    title: 'Blueprint Unlocking Missions Grouped',
    description: 'Grouped unlocking missions by drop chance. Only included on blueprint detail responses.',
    properties: [
        new OA\Property(property: 'label', description: 'Human-readable chance label (e.g. Guaranteed, 50% chance)', type: 'string'),
        new OA\Property(property: 'chance', description: 'Drop chance as a decimal', type: 'number', format: 'float', nullable: true),
        new OA\Property(
            property: 'missions',
            description: 'Missions in this chance group',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_unlocking_missions_grouped_entry')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_aspect_input',
    title: 'Blueprint Aspect Input',
    description: 'Input resource or item for a blueprint aspect.',
    properties: [
        new OA\Property(property: 'kind', description: 'Input kind', type: 'string'),
        new OA\Property(property: 'uuid', description: 'UUID of the input resource or item', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the input', type: 'string'),
        new OA\Property(property: 'quantity', description: 'Discrete count (for items)', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'quantity_scu', description: 'Quantity in Standard Cargo Units (for resources)', type: 'number', format: 'float', nullable: true),
        new OA\Property(property: 'min_quality', description: 'Minimum quality tier', type: 'integer'),
        new OA\Property(property: 'web_url', description: 'Web URL for the input detail page', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_aspect_selection_group',
    title: 'Blueprint Aspect Selection Group',
    description: 'Selection group metadata when multiple aspect options are available.',
    properties: [
        new OA\Property(property: 'key', description: 'Internal key of the selection group', type: 'string'),
        new OA\Property(property: 'name', description: 'Display name of the selection group', type: 'string'),
        new OA\Property(property: 'required_count', description: 'Number of options that must be selected', type: 'integer'),
        new OA\Property(property: 'option_count', description: 'Total number of available options', type: 'integer'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_aspect',
    title: 'Blueprint Aspect',
    description: 'A single interactive aspect with quality-dependent modifiers.',
    properties: [
        new OA\Property(property: 'key', description: 'Internal key of the aspect', type: 'string'),
        new OA\Property(property: 'name', description: 'Display name of the aspect', type: 'string'),
        new OA\Property(property: 'required_count', description: 'Required count from parent group', type: 'integer', nullable: true),
        new OA\Property(property: 'selection_group', ref: '#/components/schemas/blueprint_aspect_selection_group', nullable: true),
        new OA\Property(property: 'input', ref: '#/components/schemas/blueprint_aspect_input'),
        new OA\Property(
            property: 'modifiers',
            description: 'Quality-dependent modifier effects',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_modifier')
        ),
        new OA\Property(property: 'initial_quality', description: 'Default quality slider position', type: 'integer'),
        new OA\Property(property: 'slider_min', description: 'Minimum quality slider value', type: 'integer'),
        new OA\Property(property: 'slider_max', description: 'Maximum quality slider value', type: 'integer'),
        new OA\Property(property: 'has_modifiers', description: 'Whether this aspect has any modifiers', type: 'boolean'),
        new OA\Property(property: 'has_dynamic_modifiers', description: 'Whether modifiers change with quality', type: 'boolean'),
        new OA\Property(property: 'is_selected', description: 'Whether this aspect is selected by default', type: 'boolean'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_aspect_group',
    title: 'Blueprint Aspect Group',
    description: 'A group of related aspects, potentially a choice group.',
    properties: [
        new OA\Property(property: 'key', description: 'Internal key of the aspect group', type: 'string'),
        new OA\Property(property: 'name', description: 'Display name of the aspect group', type: 'string'),
        new OA\Property(property: 'display_name', description: 'User-facing display name, null if generic', type: 'string', nullable: true),
        new OA\Property(property: 'required_count', description: 'Number of aspects that must be selected', type: 'integer'),
        new OA\Property(property: 'option_count', description: 'Total number of aspect options', type: 'integer'),
        new OA\Property(property: 'is_choice_group', description: 'Whether this is a choice group (required < options)', type: 'boolean'),
        new OA\Property(property: 'selected_count', description: 'Number of aspects selected by default', type: 'integer'),
        new OA\Property(
            property: 'aspect_indexes',
            description: 'Indexes into the aspects array for this group',
            type: 'array',
            items: new OA\Items(type: 'integer')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'blueprint_aspects',
    title: 'Blueprint Aspects',
    description: 'Interactive aspect tree for blueprint quality simulation. Only included on blueprint detail responses.',
    properties: [
        new OA\Property(
            property: 'aspects',
            description: 'Flat list of all aspects',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_aspect')
        ),
        new OA\Property(
            property: 'aspect_groups',
            description: 'Groups of related aspects',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_aspect_group')
        ),
        new OA\Property(property: 'has_interactive_aspects', description: 'Whether any aspect has dynamic quality modifiers', type: 'boolean'),
    ],
    type: 'object'
)]

#[OA\Schema(
    schema: 'blueprint',
    title: 'Blueprint',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique blueprint identifier', type: 'string', format: 'uuid'),
        new OA\Property(property: 'key', description: 'Internal blueprint key (e.g. BP_CRAFT_behr_pistol_ballistic_01)', type: 'string'),
        new OA\Property(property: 'category_uuid', description: 'UUID of the blueprint category', type: 'string', format: 'uuid'),
        new OA\Property(property: 'output_item_uuid', description: 'UUID of the item this blueprint crafts', type: 'string', format: 'uuid'),
        new OA\Property(property: 'output_name', description: 'Display name of the crafted item', type: 'string', nullable: true),
        new OA\Property(property: 'output_class', description: 'Internal class identifier of the crafted item', type: 'string', nullable: true),
        new OA\Property(property: 'craft_time_seconds', description: 'Crafting duration in seconds', type: 'integer', nullable: true),
        new OA\Property(property: 'craft_time_label', description: 'Human-readable crafting duration', type: 'string', nullable: true),
        new OA\Property(property: 'is_available_by_default', description: 'Whether this blueprint is available without unlocking', type: 'boolean'),
        new OA\Property(property: 'game_version', description: 'Game version code this data applies to', type: 'string', nullable: true),
        new OA\Property(property: 'ingredient_count', description: 'Total number of distinct ingredients across all requirement groups', type: 'integer'),
        new OA\Property(property: 'unlocking_missions_count', description: 'Number of missions that can unlock this blueprint', type: 'integer'),
        new OA\Property(
            property: 'ingredients',
            description: 'Ingredients required to craft the item',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_ingredient')
        ),
        new OA\Property(
            property: 'dismantle_returns',
            description: 'Resources returned when dismantling the crafted item',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_dismantle_return_summary')
        ),
        new OA\Property(property: 'output', ref: '#/components/schemas/blueprint_output', description: 'Crafted item metadata'),
        new OA\Property(
            property: 'dismantle',
            ref: '#/components/schemas/blueprint_dismantle',
            description: 'Only included on blueprint detail responses.',
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
            property: 'unlocking_missions_grouped',
            description: 'Only included on blueprint detail responses.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_unlocking_missions_grouped')
        ),
        new OA\Property(
            property: 'aspects',
            ref: '#/components/schemas/blueprint_aspects',
            description: 'Only included on blueprint detail responses.',
        ),
        new OA\Property(
            property: 'tiers',
            description: 'Only included on blueprint detail responses.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/blueprint_tier')
        ),
        new OA\Property(property: 'link', description: 'API URL for this blueprint', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', description: 'Web URL for the blueprint detail page', type: 'string', format: 'uri'),
        new OA\Property(property: 'output_item_web_url', description: 'Web URL for the crafted item detail page', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
class BlueprintResource extends AbstractBaseResource
{
    private ?array $normalizedPayload = null;

    private BlueprintRequirementNormalizer $requirementNormalizer;

    public function toArray(Request $request): array
    {
        $payload = $this->rawPayload();
        $normalizer = $this->normalizer();
        $requirementGroups = $normalizer->requirementGroups($payload);
        $outputItemUuid = $this->nullableString($this->output_item_uuid);

        $this->setCanonicalResource(
            'blueprint',
            $this->blueprint->uuid,
            $this->blueprint->slug,
            $this->urlWithVersion(route('blueprints.show', ['blueprint' => $this->blueprint->uuid]), $request),
            $this->urlWithVersion(route('web.blueprints.show', ['blueprint' => $this->blueprint->slug ?? $this->blueprint->uuid]), $request),
            $this->gameVersion?->code,
        );

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
            'ingredient_count' => $this->ingredientCount($requirementGroups, $normalizer),
            'unlocking_missions_count' => $this->resource->unlocking_missions_count,
            'ingredients' => $this->ingredients($request, $normalizer, $requirementGroups),
            'dismantle_returns' => $this->dismantleReturnsList($request),
            'output' => $this->outputPayload($request, $payload),
            'web_url' => $this->urlWithVersion(route('web.blueprints.show', ['blueprint' => $this->blueprint->slug ?? $this->blueprint->uuid]), $request),
            'output_item_web_url' => $this->whenNotNull($outputItemUuid !== null && Str::isUuid($outputItemUuid)
                ? $this->urlWithVersion(route('web.items.show', ['item' => $outputItemUuid]), $request)
                : null),
            $this->mergeWhen($this->shouldIncludeDetailFields($request), [
                'dismantle' => $this->dismantlePayload($payload),
                'requirement_groups' => $this->enrichRequirementGroupsWithOreUuids($requirementGroups),
                'summary_properties' => $normalizer->summaryPropertiesFromGroups($requirementGroups),
                'unlocking_missions' => $this->unlockingMissions($request),
                'unlocking_missions_grouped' => $this->groupedUnlockingMissions($request),
                'aspects' => $this->buildAspectState($requirementGroups, $request),
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
        return $request->routeIs('blueprints.show')
            || $this->resource->relationLoaded('missions');
    }

    private function normalizer(): BlueprintRequirementNormalizer
    {
        return $this->requirementNormalizer ??= new BlueprintRequirementNormalizer;
    }

    private function buildAspectState(array $requirementGroups, Request $request): array
    {
        $makeUrl = fn (string $routeName, array $params, Request $req): string => $this->urlWithVersion(route($routeName, $params), $req);
        $ingredients = $this->loadedRelation('ingredients')->keyBy('uuid');

        return new BlueprintAspectState($makeUrl, $ingredients)->build($requirementGroups, $request);
    }

    /**
     * Enrich requirement group children with an `ore_uuid` when a raw (ore) version exists.
     *
     * @param  array<int, array<string, mixed>>  $requirementGroups
     * @return array<int, array<string, mixed>>
     */
    private function enrichRequirementGroupsWithOreUuids(array $requirementGroups): array
    {
        $ingredients = $this->loadedRelation('ingredients')->keyBy('uuid');

        return array_map(fn (array $group): array => [
            ...$group,
            'children' => $this->enrichChildrenWithOreUuids($group['children'] ?? [], $ingredients),
        ], $requirementGroups);
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @param  Collection<string, mixed>  $ingredients
     * @return array<int, array<string, mixed>>
     */
    private function enrichChildrenWithOreUuids(array $children, Collection $ingredients): array
    {
        return array_map(function (array $child) use ($ingredients): array {
            if (($child['children'] ?? []) !== []) {
                $child['children'] = $this->enrichChildrenWithOreUuids($child['children'], $ingredients);
            }

            if (($child['kind'] ?? null) === 'resource' && ($child['uuid'] ?? null) !== null) {
                $oreUuid = $ingredients->get($child['uuid'])?->rawVersions->first()?->uuid;

                if ($oreUuid !== null) {
                    $child['ore_uuid'] = $oreUuid;
                }
            }

            return $child;
        }, $children);
    }

    /**
     * @return array<int, array{title: ?string, debug_name: ?string, reward_scope: ?string, web_url: ?string}>
     */
    private function unlockingMissions(Request $request): array
    {
        $missions = $this->loadedRelation('missions');

        return $missions->map(fn ($mission): array => [
            'title' => FormatMissionText::format(
                $this->nullableString($mission->title),
                $this->nullableString($mission->debug_name),
            ),
            'debug_name' => $this->nullableString($mission->debug_name),
            'reward_scope' => $this->nullableString($mission->reward_scope),
            'chance' => $mission->pivot?->chance,
            'web_url' => $mission->relationLoaded('mission') && $mission->mission !== null
                ? $this->urlWithVersion(route('web.missions.show', ['mission' => $mission->mission->uuid]), $request)
                : null,
        ])->sortBy('title', SORT_STRING | SORT_FLAG_CASE)->values()->all();
    }

    /**
     * @return array<int, array{label: string, chance: int|float|null, missions: array<int, array{title: string, reward_scope: ?string, count: int, web_url: ?string}>}>
     */
    private function groupedUnlockingMissions(Request $request): array
    {
        $missions = $this->unlockingMissions($request);

        if ($missions === []) {
            return [];
        }

        usort($missions, static function (array $a, array $b): int {
            $chanceA = $a['chance'] ?? 0;
            $chanceB = $b['chance'] ?? 0;

            if ($chanceB !== $chanceA) {
                return $chanceB <=> $chanceA;
            }

            return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
        });

        $groups = [];

        foreach ($missions as $mission) {
            $chance = $mission['chance'] ?? null;
            $chanceKey = $chance !== null ? (string) $chance : '0';

            if (! isset($groups[$chanceKey])) {
                $groups[$chanceKey] = [
                    'label' => $chance === 1.0 ? 'Guaranteed' : ($chance !== null ? (($chance * 100).'% chance') : 'Unknown chance'),
                    'chance' => $chance,
                    'missions' => [],
                ];
            }

            $title = $mission['title'] ?? 'Unknown mission';
            $dedupKey = $title;

            if (isset($groups[$chanceKey]['dedup'][$dedupKey])) {
                $groups[$chanceKey]['missions'][$groups[$chanceKey]['dedup'][$dedupKey]]['count'] += 1;

                continue;
            }

            $groups[$chanceKey]['dedup'][$dedupKey] = count($groups[$chanceKey]['missions']);
            $groups[$chanceKey]['missions'][] = [
                'title' => $title,
                'reward_scope' => $mission['reward_scope'] ?? null,
                'count' => 1,
                'web_url' => $mission['web_url'] ?? null,
            ];
        }

        return array_values(array_map(static function (array $group): array {
            unset($group['dedup']);

            return $group;
        }, $groups));
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

        $type = $this->arrayNullableString($output, 'Type') ?? $this->arrayNullableString($output, 'type');

        return [
            'uuid' => $uuid,
            'name' => $this->arrayNullableString($output, 'Name') ?? $this->arrayNullableString($output, 'name') ?? $this->nullableString($this->output_name),
            'class' => $this->arrayNullableString($output, 'Class') ?? $this->arrayNullableString($output, 'class') ?? $this->nullableString($this->output_class),
            'type' => $type,
            'type_label' => ItemFilterLabel::resolveType($type, null),
            'sub_type' => $this->arrayNullableString($output, 'Subtype') ?? $this->arrayNullableString($output, 'subtype'),
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
            'link' => $this->urlWithVersion(route('commodities.show', ['commodity' => $commodity->uuid]), $request),
            'web_url' => $this->urlWithVersion(route('web.commodities.show', ['identifier' => $commodity->uuid]), $request),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $requirementGroups
     */
    private function ingredientCount(array $requirementGroups, BlueprintRequirementNormalizer $normalizer): int
    {
        $ingredientCount = $normalizer->ingredientCountFromGroups($requirementGroups);

        if ($ingredientCount > 0) {
            return $ingredientCount;
        }

        $ingredientResourceTypeUuids = $this->ingredients->pluck('uuid')->all();

        return count($ingredientResourceTypeUuids);
    }

    /**
     * @return array<int, array{name: ?string, resource_type_uuid: ?string, quantity_scu: int|float|null, link: ?string, web_url: ?string}>
     */
    private function ingredients(Request $request, BlueprintRequirementNormalizer $normalizer, array $requirementGroups): array
    {
        $ingredients = [];

        $normalizer->collectIngredients($requirementGroups, $ingredients);

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

        return array_values(array_map(function (array $ingredient) use ($request, $loadedIngredients): array {
            $kind = $ingredient['kind'] ?? null;

            if ($kind === 'item') {
                $uuid = $ingredient['item_uuid'] ?? null;

                if ($uuid !== null && Str::isUuid($uuid)) {
                    $ingredient = [
                        ...$ingredient,
                        'link' => $this->urlWithVersion(route('items.show', ['identifier' => $uuid]), $request),
                        'web_url' => $this->urlWithVersion(route('web.items.show', ['item' => $uuid]), $request),
                    ];
                }
            } else {
                $uuid = $ingredient['resource_type_uuid'];

                if ($uuid !== null && Str::isUuid($uuid)) {
                    $oreUuid = $loadedIngredients->get($uuid)?->rawVersions->first()?->uuid ?? $uuid;

                    $ingredient = [
                        ...$ingredient,
                        'link' => $this->urlWithVersion(route('commodities.show', ['commodity' => $oreUuid]), $request),
                        'web_url' => $this->urlWithVersion(route('web.commodities.show', ['identifier' => $oreUuid]), $request),
                    ];
                }
            }

            return $ingredient;
        }, $ingredients));
    }

    private function loadedRelation(string $relation): Collection
    {
        return $this->resource->relationLoaded($relation)
            ? $this->resource->$relation
            : collect();
    }
}
