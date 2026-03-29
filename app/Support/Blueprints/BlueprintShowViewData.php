<?php

declare(strict_types=1);

namespace App\Support\Blueprints;

use Carbon\CarbonInterval;
use Illuminate\Support\Str;

final class BlueprintShowViewData
{
    /**
     * @return array<string, mixed>
     */
    public function build(string $mode, mixed $blueprint, mixed $search, string $pageTitle): array
    {
        $resolvedMode = $mode === 'empty' ? 'empty' : 'detail';
        $isEmptyMode = $resolvedMode === 'empty';
        $normalizedBlueprint = is_array($blueprint) ? $blueprint : [];
        $normalizedSearch = is_array($search) ? $search : [];
        $searchFilters = is_array(data_get($normalizedSearch, 'filters'))
            ? data_get($normalizedSearch, 'filters')
            : [];
        $searchQuery = trim((string) ($searchFilters['query'] ?? data_get($normalizedSearch, 'query', '')));
        $searchResults = is_array(data_get($normalizedSearch, 'results'))
            ? array_values(data_get($normalizedSearch, 'results'))
            : [];
        $searchResultCount = (int) data_get($normalizedSearch, 'result_count', count($searchResults));
        $pageTitleDecoded = html_entity_decode($pageTitle);
        $resolvedVersionCode = $this->normalizeString(request()->query('version'))
            ?? $this->normalizeString(session('game_version_code'));
        $blueprintName = $this->normalizeString(data_get($normalizedBlueprint, 'output_name'))
            ?? $this->normalizeString(data_get($normalizedBlueprint, 'output.name'))
            ?? 'Blueprint';
        $blueprintKey = $this->normalizeString(data_get($normalizedBlueprint, 'key'));
        $blueprintUuid = $this->normalizeString(data_get($normalizedBlueprint, 'uuid'));
        $outputClass = $this->normalizeString(data_get($normalizedBlueprint, 'output_class'))
            ?? $this->normalizeString(data_get($normalizedBlueprint, 'output.class'));
        $outputType = $this->normalizeString(data_get($normalizedBlueprint, 'output.type'));
        $outputSubtype = $this->normalizeString(data_get($normalizedBlueprint, 'output.subtype'));
        $outputGrade = $this->normalizeString(data_get($normalizedBlueprint, 'output.grade'));
        $craftTimeSeconds = data_get($normalizedBlueprint, 'craft_time_seconds');
        $formatCraftTime = static fn (mixed $seconds): ?string => self::formatCraftTime($seconds);
        $craftTimeLabel = self::formatCraftTime($craftTimeSeconds);
        $ingredientCount = (int) data_get($normalizedBlueprint, 'ingredient_count', 0);
        $requirementGroups = is_array(data_get($normalizedBlueprint, 'requirement_groups'))
            ? data_get($normalizedBlueprint, 'requirement_groups')
            : [];
        $ingredients = $this->resolveIngredients($normalizedBlueprint, $requirementGroups);
        $summaryProperties = is_array(data_get($normalizedBlueprint, 'summary_properties'))
            ? data_get($normalizedBlueprint, 'summary_properties')
            : [];
        $rewardPools = is_array(data_get($normalizedBlueprint, 'availability.reward_pools'))
            ? data_get($normalizedBlueprint, 'availability.reward_pools')
            : [];
        $isAvailableByDefault = (bool) data_get(
            $normalizedBlueprint,
            'availability.default',
            data_get($normalizedBlueprint, 'is_available_by_default', false),
        );
        $outputItemWebUrl = $this->normalizeString(data_get($normalizedBlueprint, 'output.item_web_url'))
            ?? $this->normalizeString(data_get($normalizedBlueprint, 'output_item_web_url'));
        $apiLink = $this->normalizeString(data_get($normalizedBlueprint, 'link'));
        $unlockSources = $this->buildUnlockSources($rewardPools);
        $canonicalUrl = $isEmptyMode
            ? route('web.blueprints.search', array_filter([
                'version' => $resolvedVersionCode,
            ]))
            : ($this->normalizeString(data_get($normalizedBlueprint, 'web_url')) ?? url()->current());
        $metaDescription = $isEmptyMode
            ? 'Search Star Citizen blueprints by output name, class, item, or input resource.'
            : Str::limit(
                trim(collect([
                    $blueprintName.' blueprint',
                    $outputType !== null ? 'type '.$outputType : null,
                    $craftTimeSeconds !== null ? 'craft time '.$craftTimeSeconds.' seconds' : null,
                    $ingredientCount > 0 ? $ingredientCount.' inputs' : null,
                ])->filter()->implode(', ')),
                160,
            );
        $rawBlueprintJson = $isEmptyMode ? '{}' : $this->encodeJson($normalizedBlueprint);
        $metaTitle = $isEmptyMode ? 'Search Blueprints - Star Citizen' : $blueprintName.' Blueprint';
        $searchApiEndpoint = $this->normalizeString(data_get($normalizedSearch, 'api_endpoint'))
            ?? route('blueprints.index', [], false);
        $resourceTypesEndpoint = $this->normalizeString(data_get($normalizedSearch, 'resource_types_endpoint'))
            ?? route('resource-types.index', ['filter' => ['used' => 'true']], false);
        $selectedIngredientResourceTypeUuids = array_values(array_filter(
            array_map(
                static fn (string $uuid): string => trim($uuid),
                explode(',', (string) ($searchFilters['ingredient.uuid'] ?? '')),
            ),
            static fn (string $uuid): bool => Str::isUuid($uuid),
        ));
        $initialSearchResults = $this->buildInitialSearchResults(
            isEmptyMode: $isEmptyMode,
            searchResults: $searchResults,
            blueprint: $normalizedBlueprint,
            blueprintUuid: $blueprintUuid,
            blueprintKey: $blueprintKey,
            blueprintName: $blueprintName,
            outputClass: $outputClass,
            craftTimeSeconds: $craftTimeSeconds,
            ingredientCount: $ingredientCount,
            ingredients: $ingredients,
            outputType: $outputType,
            outputSubtype: $outputSubtype,
            canonicalUrl: $canonicalUrl,
        );
        $hasSearchFilters = $searchQuery !== '' || $selectedIngredientResourceTypeUuids !== [];
        $renderSearchResultCount = $hasSearchFilters ? $searchResultCount : count($initialSearchResults);
        $summaryPropertyList = $this->normalizeArrayList($summaryProperties);
        $aspectState = $this->buildAspectState($requirementGroups);
        $clientPayload = $this->encodeHtmlSafeJson([
            'search' => [
                'apiEndpoint' => $searchApiEndpoint,
                'resourceTypesEndpoint' => $resourceTypesEndpoint,
                'query' => $searchQuery,
                'version' => $resolvedVersionCode,
                'currentBlueprintUuid' => $blueprintUuid,
                'selectedResourceTypeUuids' => $selectedIngredientResourceTypeUuids,
                'initialResults' => $initialSearchResults,
                'initialResultCount' => $renderSearchResultCount,
            ],
            'detail' => $isEmptyMode ? null : [
                'hasInteractiveAspects' => $aspectState['hasInteractiveAspects'],
                'summaryProperties' => $summaryPropertyList,
                'aspects' => array_values($aspectState['aspects']),
            ],
        ]);

        return [
            'mode' => $resolvedMode,
            'isEmptyMode' => $isEmptyMode,
            'blueprint' => $normalizedBlueprint,
            'search' => $normalizedSearch,
            'pageTitle' => $pageTitle,
            'pageTitleDecoded' => $pageTitleDecoded,
            'searchQuery' => $searchQuery,
            'blueprintName' => $blueprintName,
            'blueprintKey' => $blueprintKey,
            'blueprintUuid' => $blueprintUuid,
            'outputClass' => $outputClass,
            'outputType' => $outputType,
            'outputSubtype' => $outputSubtype,
            'outputGrade' => $outputGrade,
            'craftTimeLabel' => $craftTimeLabel,
            'requirementGroups' => $requirementGroups,
            'rewardPools' => $rewardPools,
            'isAvailableByDefault' => $isAvailableByDefault,
            'outputItemWebUrl' => $outputItemWebUrl,
            'apiLink' => $apiLink,
            'unlockSources' => $unlockSources,
            'canonicalUrl' => $canonicalUrl,
            'metaDescription' => $metaDescription,
            'rawBlueprintJson' => $rawBlueprintJson,
            'metaTitle' => $metaTitle,
            'resolvedVersionCode' => $resolvedVersionCode,
            'selectedIngredientResourceTypeUuids' => $selectedIngredientResourceTypeUuids,
            'initialSearchResults' => $initialSearchResults,
            'hasSearchFilters' => $hasSearchFilters,
            'renderSearchResultCount' => $renderSearchResultCount,
            'summaryPropertyList' => $summaryPropertyList,
            'hasInteractiveAspects' => $aspectState['hasInteractiveAspects'],
            'aspects' => $aspectState['aspects'],
            'aspectGroups' => $aspectState['aspectGroups'],
            'clientPayload' => $clientPayload,
            'formatCraftTime' => $formatCraftTime,
            'formatAspectAmount' => static fn (array $aspect): ?string => self::formatAspectAmount($aspect),
            'formatAspectQuality' => static fn (array $aspect): string => self::formatAspectQuality($aspect),
            'resolveRequirementLabel' => static fn (mixed $name, mixed $key, string $fallback = 'Aspect'): string => self::resolveRequirementLabel($name, $key, $fallback),
        ];
    }

    /**
     * @param  array<int, mixed>  $rewardPools
     * @return array<int, array{label: string, type: string, key: ?string, uuid: ?string}>
     */
    private function buildUnlockSources(array $rewardPools): array
    {
        return collect($rewardPools)
            ->map(function (mixed $rewardPool): ?array {
                if (! is_array($rewardPool)) {
                    return null;
                }

                $key = $this->normalizeString(data_get($rewardPool, 'key'));
                $uuid = $this->normalizeString(data_get($rewardPool, 'uuid'));

                if ($key === null && $uuid === null) {
                    return null;
                }

                $labelSource = $key ?? $uuid ?? 'Unlock source';
                $label = Str::headline(str_replace([
                    'BP_MISSIONREWARD_',
                    'BP_REWARD_',
                    'MISSIONREWARD_',
                    'REWARD_',
                    'BP_',
                ], '', $labelSource));

                return [
                    'label' => $label !== '' ? $label : 'Unlock source',
                    'type' => $key !== null && Str::startsWith($key, 'BP_MISSIONREWARD_')
                        ? 'Mission reward'
                        : 'Unlock source',
                    'key' => $key,
                    'uuid' => $uuid,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $searchResults
     * @param  array<string, mixed>  $blueprint
     * @return array<int, mixed>
     */
    private function buildInitialSearchResults(
        bool $isEmptyMode,
        array $searchResults,
        array $blueprint,
        ?string $blueprintUuid,
        ?string $blueprintKey,
        string $blueprintName,
        ?string $outputClass,
        mixed $craftTimeSeconds,
        int $ingredientCount,
        array $ingredients,
        ?string $outputType,
        ?string $outputSubtype,
        string $canonicalUrl,
    ): array {
        if ($isEmptyMode || $searchResults !== [] || $blueprintUuid === null) {
            return $searchResults;
        }

        return [[
            'uuid' => $blueprintUuid,
            'key' => $blueprintKey,
            'output_name' => $blueprintName,
            'output_class' => $outputClass,
            'craft_time_seconds' => $craftTimeSeconds,
            'ingredient_count' => $ingredientCount,
            'ingredients' => $ingredients,
            'output' => [
                'type' => $outputType,
                'subtype' => $outputSubtype,
            ],
            'web_url' => $canonicalUrl,
        ]];
    }

    /**
     * @param  array<int, mixed>  $requirementGroups
     * @return array{
     *     aspects: array<int, array<string, mixed>>,
     *     aspectGroups: array<int, array<string, mixed>>,
     *     hasInteractiveAspects: bool
     * }
     */
    private function buildAspectState(array $requirementGroups): array
    {
        $aspects = [];

        foreach ($requirementGroups as $group) {
            if (! is_array($group)) {
                continue;
            }

            $groupName = self::resolveRequirementLabel(data_get($group, 'name'), data_get($group, 'key'), 'Aspect');
            $groupKey = $this->normalizeString(data_get($group, 'key'));
            $groupRequiredCount = is_numeric(data_get($group, 'required_count'))
                ? (int) data_get($group, 'required_count')
                : null;
            $groupChildren = is_array(data_get($group, 'children')) ? data_get($group, 'children') : [];
            $groupLeafOptionCount = $this->countRenderableLeaves($groupChildren);
            $groupModifiers = $this->normalizeArrayList(data_get($group, 'modifiers'));
            $topLevelSelectionGroup = null;

            if ($groupRequiredCount !== null && $groupLeafOptionCount > 1 && $groupRequiredCount < $groupLeafOptionCount) {
                $topLevelSelectionGroup = [
                    'key' => $groupKey ?? Str::slug($groupName),
                    'name' => $groupName,
                    'required_count' => $groupRequiredCount,
                    'option_count' => $groupLeafOptionCount,
                ];
            }

            $aspects = [
                ...$aspects,
                ...$this->extractAspectNodes(
                    $groupChildren,
                    $groupModifiers,
                    $groupName,
                    $groupKey,
                    $groupRequiredCount,
                    $topLevelSelectionGroup,
                ),
            ];
        }

        $hasInteractiveAspects = false;

        foreach ($aspects as $aspectIndex => $aspect) {
            $modifiers = is_array($aspect['modifiers'] ?? null) ? $aspect['modifiers'] : [];
            $sliderMin = max(0, (int) data_get($aspect, 'input.min_quality', 0));
            $sliderMax = 1000;
            $hasModifiers = $modifiers !== [];
            $hasDynamicModifiers = false;

            foreach ($modifiers as $modifier) {
                $qualityMin = data_get($modifier, 'quality_range.min');
                $qualityMax = data_get($modifier, 'quality_range.max');
                $atMinQuality = data_get($modifier, 'modifier_range.at_min_quality');
                $atMaxQuality = data_get($modifier, 'modifier_range.at_max_quality');

                if (is_numeric($qualityMin)) {
                    $sliderMin = max($sliderMin, (int) floor((float) $qualityMin));
                }

                if (is_numeric($qualityMax)) {
                    $sliderMax = max($sliderMin, (int) ceil((float) $qualityMax));
                }

                if (is_numeric($atMinQuality) && is_numeric($atMaxQuality) && (float) $atMinQuality !== (float) $atMaxQuality) {
                    $hasDynamicModifiers = true;
                }
            }

            if ($sliderMax < $sliderMin) {
                $sliderMax = $sliderMin;
            }

            $initialQuality = min(max(500, $sliderMin), $sliderMax);
            $aspects[$aspectIndex] = [
                ...$aspect,
                'initial_quality' => $initialQuality,
                'slider_min' => $sliderMin,
                'slider_max' => $sliderMax,
                'has_modifiers' => $hasModifiers,
                'has_dynamic_modifiers' => $hasDynamicModifiers,
            ];

            if ($hasDynamicModifiers) {
                $hasInteractiveAspects = true;
            }
        }

        $aspectGroups = [];

        foreach ($aspects as $aspectIndex => $aspect) {
            $selectionGroup = is_array($aspect['selection_group'] ?? null) ? $aspect['selection_group'] : null;
            $selectionKey = $selectionGroup !== null && is_string(data_get($selectionGroup, 'key'))
                ? trim((string) data_get($selectionGroup, 'key'))
                : '';
            $selectionKey = $selectionKey !== '' ? $selectionKey : '__aspect_'.$aspectIndex;
            $optionCount = $selectionGroup !== null && is_numeric(data_get($selectionGroup, 'option_count'))
                ? max(1, (int) data_get($selectionGroup, 'option_count'))
                : 1;
            $requiredSelectionCount = $selectionGroup !== null && is_numeric(data_get($selectionGroup, 'required_count'))
                ? max(1, min((int) data_get($selectionGroup, 'required_count'), $optionCount))
                : 1;
            $isChoiceGroup = $selectionGroup !== null && $requiredSelectionCount < $optionCount;

            if (! array_key_exists($selectionKey, $aspectGroups)) {
                $selectionGroupName = $isChoiceGroup
                    ? self::resolveRequirementLabel(data_get($selectionGroup, 'name'), data_get($selectionGroup, 'key'), 'Input set')
                    : (is_string($aspect['name'] ?? null) ? $aspect['name'] : 'Aspect');
                $selectionGroupDisplayName = $isChoiceGroup && self::isGenericSelectionGroup(
                    data_get($selectionGroup, 'name'),
                    data_get($selectionGroup, 'key'),
                )
                    ? null
                    : $selectionGroupName;

                $aspectGroups[$selectionKey] = [
                    'key' => $selectionKey,
                    'name' => $selectionGroupName,
                    'display_name' => $selectionGroupDisplayName,
                    'required_count' => $requiredSelectionCount,
                    'option_count' => $optionCount,
                    'is_choice_group' => $isChoiceGroup,
                    'selected_count' => 0,
                    'aspects' => [],
                ];
            }

            $isSelected = $aspectGroups[$selectionKey]['selected_count'] < $requiredSelectionCount;
            $aspects[$aspectIndex]['is_selected'] = $isSelected;
            $aspectGroups[$selectionKey]['selected_count'] += $isSelected ? 1 : 0;
            $aspectGroups[$selectionKey]['aspects'][] = [
                'index' => $aspectIndex,
                ...$aspects[$aspectIndex],
            ];
        }

        return [
            'aspects' => array_values($aspects),
            'aspectGroups' => array_values($aspectGroups),
            'hasInteractiveAspects' => $hasInteractiveAspects,
        ];
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @param  array<int, array<string, mixed>>  $inheritedModifiers
     * @return array<int, array<string, mixed>>
     */
    private function extractAspectNodes(
        array $nodes,
        array $inheritedModifiers = [],
        ?string $aspectName = null,
        ?string $aspectKey = null,
        ?int $requiredCount = null,
        ?array $selectionGroup = null,
    ): array {
        $aspects = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $kind = (string) data_get($node, 'kind', '');
            $nodeModifiers = $this->normalizeArrayList(data_get($node, 'modifiers'));
            $combinedModifiers = [...$inheritedModifiers, ...$nodeModifiers];

            if ($kind === 'group') {
                $nestedName = data_get($node, 'name');
                $nestedKey = $this->normalizeString(data_get($node, 'key'));
                $nestedRequiredCount = data_get($node, 'required_count');
                $children = is_array(data_get($node, 'children')) ? data_get($node, 'children') : [];
                $resolvedAspectName = self::resolveRequirementLabel($nestedName, $nestedKey, $aspectName ?? 'Aspect');
                $resolvedAspectKey = $nestedKey ?? $aspectKey;
                $resolvedRequiredCount = is_numeric($nestedRequiredCount) ? (int) $nestedRequiredCount : $requiredCount;
                $nextSelectionGroup = $selectionGroup;
                $leafOptionCount = $this->countRenderableLeaves($children);

                if ($leafOptionCount > 1 && $resolvedRequiredCount !== null && $resolvedRequiredCount < $leafOptionCount) {
                    $selectionKey = $resolvedAspectKey ?? Str::slug($resolvedAspectName);

                    $nextSelectionGroup = [
                        'key' => $selectionKey !== '' ? $selectionKey : Str::slug($resolvedAspectName ?: 'input-set'),
                        'name' => $resolvedAspectName,
                        'required_count' => $resolvedRequiredCount,
                        'option_count' => $leafOptionCount,
                    ];
                }

                $aspects = [
                    ...$aspects,
                    ...$this->extractAspectNodes(
                        $children,
                        $combinedModifiers,
                        $resolvedAspectName,
                        $resolvedAspectKey,
                        $resolvedRequiredCount,
                        $nextSelectionGroup,
                    ),
                ];

                continue;
            }

            $inputName = data_get($node, 'name') ?? 'Unknown input';

            $aspects[] = [
                'key' => $aspectKey ?? data_get($node, 'key') ?? Str::slug((string) $inputName),
                'name' => $aspectName ?? $inputName,
                'required_count' => $requiredCount,
                'selection_group' => $selectionGroup,
                'input' => [
                    'kind' => $kind !== '' ? $kind : 'input',
                    'uuid' => data_get($node, 'uuid'),
                    'name' => $inputName,
                    'quantity' => is_numeric(data_get($node, 'quantity')) ? data_get($node, 'quantity') + 0 : null,
                    'quantity_scu' => is_numeric(data_get($node, 'quantity_scu')) ? data_get($node, 'quantity_scu') + 0 : null,
                    'min_quality' => is_numeric(data_get($node, 'min_quality')) ? (int) data_get($node, 'min_quality') : 0,
                ],
                'modifiers' => $combinedModifiers,
            ];
        }

        return $aspects;
    }

    /**
     * @param  array<int, mixed>  $nodes
     */
    private function countRenderableLeaves(array $nodes): int
    {
        $count = 0;

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if ((string) data_get($node, 'kind', '') === 'group') {
                $children = is_array(data_get($node, 'children')) ? data_get($node, 'children') : [];
                $count += $this->countRenderableLeaves($children);

                continue;
            }

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, mixed>  $requirementGroups
     * @return array<int, array{name: ?string, resource_type_uuid: ?string}>
     */
    private function resolveIngredients(array $blueprint, array $requirementGroups): array
    {
        $ingredients = $this->normalizeIngredientList(data_get($blueprint, 'ingredients'));

        if ($ingredients !== []) {
            return $ingredients;
        }

        foreach ($this->normalizeStringList(data_get($blueprint, 'ingredient_names')) as $ingredientName) {
            $this->pushIngredient($ingredients, $ingredientName, null);
        }

        foreach ($this->normalizeStringList(data_get($blueprint, 'ingredient_resource_type_uuids')) as $resourceTypeUuid) {
            $this->pushIngredient($ingredients, null, $resourceTypeUuid);
        }

        if ($ingredients !== []) {
            return array_values($ingredients);
        }

        $this->collectIngredients($requirementGroups, $ingredients);

        return array_values($ingredients);
    }

    /**
     * @param  array<int, mixed>  $nodes
     * @param  array<string, array{name: ?string, resource_type_uuid: ?string}>  $ingredients
     */
    private function collectIngredients(array $nodes, array &$ingredients): void
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $children = is_array(data_get($node, 'children')) ? data_get($node, 'children') : [];

            if ((string) data_get($node, 'kind', '') === 'group' || $children !== []) {
                $this->collectIngredients($children, $ingredients);

                continue;
            }

            $ingredientName = $this->normalizeString(data_get($node, 'name'))
                ?? $this->normalizeString(data_get($node, 'key'));
            $resourceTypeUuid = (string) data_get($node, 'kind', '') === 'resource'
                ? $this->normalizeString(data_get($node, 'uuid'))
                : null;

            $this->pushIngredient($ingredients, $ingredientName, $resourceTypeUuid);
        }
    }

    /**
     * @param  array<string, array{name: ?string, resource_type_uuid: ?string}>  $ingredients
     */
    private function pushIngredient(array &$ingredients, ?string $name, ?string $resourceTypeUuid): void
    {
        $ingredientKey = $resourceTypeUuid ?? $name;

        if ($ingredientKey === null) {
            return;
        }

        $ingredients[$ingredientKey] ??= [
            'name' => $name,
            'resource_type_uuid' => $resourceTypeUuid,
        ];
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeArrayList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, static fn (mixed $item): bool => is_array($item)));
    }

    /**
     * @return array<int, array{name: ?string, resource_type_uuid: ?string}>
     */
    private function normalizeIngredientList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $ingredients = [];

        foreach ($value as $ingredient) {
            if (! is_array($ingredient)) {
                continue;
            }

            $this->pushIngredient(
                $ingredients,
                $this->normalizeString(data_get($ingredient, 'name')),
                $this->normalizeString(data_get($ingredient, 'resource_type_uuid')),
            );
        }

        return array_values($ingredients);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalizedValues = [];

        foreach ($value as $item) {
            $normalizedItem = $this->normalizeString($item);

            if ($normalizedItem !== null && ! in_array($normalizedItem, $normalizedValues, true)) {
                $normalizedValues[] = $normalizedItem;
            }
        }

        return $normalizedValues;
    }

    private function encodeJson(mixed $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function encodeHtmlSafeJson(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT,
        ) ?: '{}';
    }

    private static function formatCraftTime(mixed $seconds): ?string
    {
        if (! is_numeric($seconds)) {
            return null;
        }

        $normalizedSeconds = max(0, (int) round((float) $seconds));

        if ($normalizedSeconds <= 60) {
            return $normalizedSeconds.' seconds';
        }

        return CarbonInterval::seconds($normalizedSeconds)->cascade()->forHumans([
            'parts' => 2,
        ]);
    }

    private static function formatAspectAmount(array $aspect): ?string
    {
        $input = is_array($aspect['input'] ?? null) ? $aspect['input'] : [];
        $quantityScu = $input['quantity_scu'] ?? null;

        if (is_numeric($quantityScu)) {
            return number_format((float) $quantityScu, 2).' SCU';
        }

        $quantity = $input['quantity'] ?? null;

        if (! is_numeric($quantity)) {
            return null;
        }

        $normalizedQuantity = (float) $quantity;
        $quantityLabel = fmod($normalizedQuantity, 1.0) === 0.0
            ? (string) (int) $normalizedQuantity
            : rtrim(rtrim(number_format($normalizedQuantity, 2, '.', ''), '0'), '.');

        if (($input['kind'] ?? null) === 'item') {
            return $quantityLabel.' '.($normalizedQuantity === 1.0 ? 'item' : 'items');
        }

        return $quantityLabel;
    }

    private static function formatAspectQuality(array $aspect): string
    {
        if (($aspect['is_selected'] ?? true) === false) {
            return 'Excluded';
        }

        return 'Q'.(int) ($aspect['initial_quality'] ?? 500);
    }

    private static function resolveRequirementLabel(mixed $name, mixed $key, string $fallback = 'Aspect'): string
    {
        $normalizedName = is_string($name) ? trim($name) : '';

        if ($normalizedName !== '' && ! Str::contains(Str::upper($normalizedName), 'PLACEHOLDER')) {
            return $normalizedName;
        }

        $normalizedKey = is_string($key) ? trim($key) : '';

        if ($normalizedKey !== '') {
            return Str::headline($normalizedKey);
        }

        return $fallback;
    }

    private static function isGenericSelectionGroup(mixed $name, mixed $key): bool
    {
        $normalizedKey = is_string($key) ? Str::upper(trim($key)) : '';
        $resolvedName = Str::upper(self::resolveRequirementLabel($name, $key, ''));

        return in_array($normalizedKey, ['ASPECT', 'ASPECTS'], true)
            || in_array($resolvedName, ['ASPECT', 'ASPECTS'], true);
    }
}
