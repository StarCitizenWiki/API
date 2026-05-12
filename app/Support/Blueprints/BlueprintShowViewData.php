<?php

declare(strict_types=1);

namespace App\Support\Blueprints;

use App\Support\Format;
use App\Support\Formatting\FormatDuration;
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
        $pageTitleDecoded = html_entity_decode($pageTitle);
        $rawVersion = request()->query('version');
        $resolvedVersionCode = (is_string($rawVersion) && trim($rawVersion) !== '' ? trim($rawVersion) : null)
            ?? session('game_version_code');

        $blueprintName = data_get($normalizedBlueprint, 'output_name')
            ?? data_get($normalizedBlueprint, 'output.name')
            ?? 'Blueprint';
        $blueprintKey = data_get($normalizedBlueprint, 'key');
        $blueprintUuid = data_get($normalizedBlueprint, 'uuid');
        $outputClass = data_get($normalizedBlueprint, 'output_class')
            ?? data_get($normalizedBlueprint, 'output.class');
        $outputType = data_get($normalizedBlueprint, 'output.type');
        $outputSubtype = data_get($normalizedBlueprint, 'output.subtype');
        $outputGrade = data_get($normalizedBlueprint, 'output.grade');
        $craftTimeSeconds = data_get($normalizedBlueprint, 'craft_time_seconds');
        $craftTimeLabel = data_get($normalizedBlueprint, 'craft_time_label')
            ?? self::formatCraftTime($craftTimeSeconds);
        $ingredientCount = (int) data_get($normalizedBlueprint, 'ingredient_count', 0);
        $requirementGroups = is_array(data_get($normalizedBlueprint, 'requirement_groups'))
            ? data_get($normalizedBlueprint, 'requirement_groups')
            : [];
        $ingredients = $this->resolveIngredients($normalizedBlueprint, $requirementGroups);
        $summaryProperties = is_array(data_get($normalizedBlueprint, 'summary_properties'))
            ? data_get($normalizedBlueprint, 'summary_properties')
            : [];
        $isAvailableByDefault = (bool) data_get(
            $normalizedBlueprint,
            'is_available_by_default',
            false,
        );
        $outputItemWebUrl = data_get($normalizedBlueprint, 'output.item_web_url')
            ?? data_get($normalizedBlueprint, 'output_item_web_url');
        $apiLink = data_get($normalizedBlueprint, 'link');
        $unlockingMissions = is_array(data_get($normalizedBlueprint, 'unlocking_missions_grouped'))
            ? data_get($normalizedBlueprint, 'unlocking_missions_grouped')
            : [];

        $dismantleData = $this->buildDismantleData($normalizedBlueprint);
        $canonicalUrl = $isEmptyMode
            ? route('web.blueprints.search', array_filter([
                'version' => $resolvedVersionCode,
            ]))
            : (data_get($normalizedBlueprint, 'web_url') ?? url()->current());
        $searchState = $this->buildSearchState(
            $normalizedSearch,
            $normalizedBlueprint,
            $isEmptyMode,
            blueprintName: $blueprintName,
            blueprintKey: $blueprintKey,
            blueprintUuid: $blueprintUuid,
            outputClass: $outputClass,
            outputType: $outputType,
            outputSubtype: $outputSubtype,
            craftTimeSeconds: $craftTimeSeconds,
            ingredientCount: $ingredientCount,
            ingredients: $ingredients,
            canonicalUrl: $canonicalUrl,
        );
        $rawBlueprintJson = $isEmptyMode ? '{}' : $this->encodeJson($normalizedBlueprint);

        $aspectState = $this->resolveAspectState($normalizedBlueprint, $isEmptyMode);
        $summaryPropertyList = $this->normalizeArrayList($summaryProperties);
        $hasSearchFilters = $searchState['searchQuery'] !== '' || $searchState['selectedIngredientResourceTypeUuids'] !== [];
        $renderSearchResultCount = $hasSearchFilters ? $searchState['searchResultCount'] : count($searchState['initialSearchResults']);
        $clientPayload = $this->encodeHtmlSafeJson([
            'search' => [
                'apiEndpoint' => $searchState['searchApiEndpoint'],
                'resourceTypesEndpoint' => $searchState['resourceTypesEndpoint'],
                'query' => $searchState['searchQuery'],
                'version' => $resolvedVersionCode,
                'currentBlueprintUuid' => $blueprintUuid,
                'selectedResourceTypeUuids' => $searchState['selectedIngredientResourceTypeUuids'],
                'initialResults' => $searchState['initialSearchResults'],
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
            'searchQuery' => $searchState['searchQuery'],
            'blueprintName' => $blueprintName,
            'blueprintKey' => $blueprintKey,
            'blueprintUuid' => $blueprintUuid,
            'outputClass' => $outputClass,
            'outputType' => $outputType,
            'outputSubtype' => $outputSubtype,
            'outputGrade' => $outputGrade,
            'craftTimeLabel' => $craftTimeLabel,
            'requirementGroups' => $requirementGroups,
            'isAvailableByDefault' => $isAvailableByDefault,
            'outputItemWebUrl' => $outputItemWebUrl,
            'apiLink' => $apiLink,
            'unlockingMissions' => $unlockingMissions,
            'hasDismantleData' => $dismantleData['hasDismantleData'],
            'dismantleTimeLabel' => $dismantleData['dismantleTimeLabel'],
            'dismantleEfficiency' => $dismantleData['dismantleEfficiency'],
            'dismantleReturns' => $dismantleData['dismantleReturns'],
            'rawBlueprintJson' => $rawBlueprintJson,
            'resolvedVersionCode' => $resolvedVersionCode,
            'selectedIngredientResourceTypeUuids' => $searchState['selectedIngredientResourceTypeUuids'],
            'initialSearchResults' => $searchState['initialSearchResults'],
            'hasSearchFilters' => $hasSearchFilters,
            'renderSearchResultCount' => $renderSearchResultCount,
            'summaryPropertyList' => $summaryPropertyList,
            'hasInteractiveAspects' => $aspectState['hasInteractiveAspects'],
            'aspects' => $aspectState['aspects'],
            'aspectGroups' => $aspectState['aspectGroups'],
            'clientPayload' => $clientPayload,
            'formatCraftTime' => static fn (mixed $seconds): ?string => self::formatCraftTime($seconds),
            'formatAspectAmount' => static fn (array $aspect): ?string => self::formatAspectAmount($aspect),
            'formatAspectQuality' => static fn (array $aspect): string => self::formatAspectQuality($aspect),
            'resolveRequirementLabel' => static fn (mixed $name, mixed $key, string $fallback = 'Aspect'): string => self::resolveRequirementLabel($name, $key, $fallback),
        ];
    }

    /**
     * @return array{
     *     hasDismantleData: bool,
     *     dismantleTimeLabel: ?string,
     *     dismantleEfficiency: int|float|null,
     *     dismantleReturns: array<int, array<string, mixed>>,
     * }
     */
    private function buildDismantleData(array $normalizedBlueprint): array
    {
        $dismantle = is_array(data_get($normalizedBlueprint, 'dismantle'))
            ? data_get($normalizedBlueprint, 'dismantle')
            : [];
        $dismantleTimeSeconds = data_get($dismantle, 'time_seconds');
        $dismantleTimeLabel = data_get($dismantle, 'time_label')
            ?? self::formatCraftTime($dismantleTimeSeconds);
        $dismantleEfficiency = is_numeric(data_get($dismantle, 'efficiency'))
            ? data_get($dismantle, 'efficiency') + 0
            : null;
        $dismantleReturns = is_array(data_get($normalizedBlueprint, 'dismantle_returns'))
            ? array_values(array_filter(
                array_map(static function (mixed $return): ?array {
                    if (! is_array($return)) {
                        return null;
                    }

                    return [
                        'name' => is_string(data_get($return, 'name')) ? data_get($return, 'name') : null,
                        'resource_type_uuid' => is_string(data_get($return, 'resource_type_uuid')) ? data_get($return, 'resource_type_uuid') : null,
                        'quantity_scu' => is_numeric(data_get($return, 'quantity_scu')) ? data_get($return, 'quantity_scu') + 0 : null,
                        'web_url' => is_string(data_get($return, 'web_url')) ? data_get($return, 'web_url') : null,
                    ];
                }, data_get($normalizedBlueprint, 'dismantle_returns')),
                static fn (?array $return): bool => $return !== null,
            ))
            : [];
        $hasDismantleData = $dismantleTimeSeconds !== null
            || $dismantleEfficiency !== null
            || $dismantleReturns !== [];

        return [
            'hasDismantleData' => $hasDismantleData,
            'dismantleTimeLabel' => $dismantleTimeLabel,
            'dismantleEfficiency' => $dismantleEfficiency,
            'dismantleReturns' => $dismantleReturns,
        ];
    }

    /**
     * @return array{
     *     searchApiEndpoint: string,
     *     resourceTypesEndpoint: string,
     *     searchQuery: string,
     *     searchResultCount: int,
     *     selectedIngredientResourceTypeUuids: array<int, string>,
     *     initialSearchResults: array<int, mixed>,
     * }
     */
    private function buildSearchState(
        array $normalizedSearch,
        array $normalizedBlueprint,
        bool $isEmptyMode,
        ?string $blueprintName,
        ?string $blueprintKey,
        ?string $blueprintUuid,
        ?string $outputClass,
        ?string $outputType,
        ?string $outputSubtype,
        mixed $craftTimeSeconds,
        int $ingredientCount,
        array $ingredients,
        string $canonicalUrl,
    ): array {
        $searchFilters = is_array(data_get($normalizedSearch, 'filters'))
            ? data_get($normalizedSearch, 'filters')
            : [];
        $searchQuery = trim((string) ($searchFilters['query'] ?? data_get($normalizedSearch, 'query', '')));
        $searchResults = is_array(data_get($normalizedSearch, 'results'))
            ? array_values(data_get($normalizedSearch, 'results'))
            : [];
        $searchResultCount = (int) data_get($normalizedSearch, 'result_count', count($searchResults));
        $searchApiEndpoint = data_get($normalizedSearch, 'api_endpoint')
            ?? route('blueprints.index', [], false);
        $resourceTypesEndpoint = data_get($normalizedSearch, 'resource_types_endpoint')
            ?? route('commodities.index', ['filter' => ['used' => 'true']], false);
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

        return [
            'searchApiEndpoint' => $searchApiEndpoint,
            'resourceTypesEndpoint' => $resourceTypesEndpoint,
            'searchQuery' => $searchQuery,
            'searchResultCount' => $searchResultCount,
            'selectedIngredientResourceTypeUuids' => $selectedIngredientResourceTypeUuids,
            'initialSearchResults' => $initialSearchResults,
        ];
    }

    /**
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
     * @return array{aspects: array<int, array<string, mixed>>, aspectGroups: array<int, array<string, mixed>>, hasInteractiveAspects: bool}
     */
    private function resolveAspectState(array $normalizedBlueprint, bool $isEmptyMode): array
    {
        if ($isEmptyMode) {
            return [
                'aspects' => [],
                'aspectGroups' => [],
                'hasInteractiveAspects' => false,
            ];
        }

        $aspectsData = data_get($normalizedBlueprint, 'aspects');

        if (is_array($aspectsData)
            && array_key_exists('aspects', $aspectsData)
            && array_key_exists('aspect_groups', $aspectsData)
            && array_key_exists('has_interactive_aspects', $aspectsData)
        ) {
            return [
                'aspects' => $aspectsData['aspects'],
                'aspectGroups' => $aspectsData['aspect_groups'],
                'hasInteractiveAspects' => $aspectsData['has_interactive_aspects'],
            ];
        }

        return [
            'aspects' => [],
            'aspectGroups' => [],
            'hasInteractiveAspects' => false,
        ];
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

        foreach ($this->normalizeIngredientList(data_get($blueprint, 'ingredients')) as $ingredient) {
            $uuid = data_get($ingredient, 'resource_type_uuid');
            $name = data_get($ingredient, 'name');

            if ($uuid !== null || $name !== null) {
                $this->pushIngredient($ingredients, $name, $uuid);
            }
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

            $ingredientName = data_get($node, 'name')
                ?? data_get($node, 'key');
            $resourceTypeUuid = (string) data_get($node, 'kind', '') === 'resource'
                ? data_get($node, 'uuid')
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
                data_get($ingredient, 'name'),
                data_get($ingredient, 'resource_type_uuid'),
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
            if (! is_string($item) || $item === '' || in_array($item, $normalizedValues, true)) {
                continue;
            }

            $normalizedValues[] = $item;
        }

        return $normalizedValues;
    }

    private function encodeJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    private function encodeHtmlSafeJson(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT) ?: '{}';
    }

    private static function formatCraftTime(mixed $seconds): ?string
    {
        return FormatDuration::fromSeconds($seconds);
    }

    private static function formatAspectAmount(array $aspect): ?string
    {
        $input = is_array($aspect['input'] ?? null) ? $aspect['input'] : [];
        $quantityScu = $input['quantity_scu'] ?? null;

        if (is_numeric($quantityScu)) {
            return Format::number((float) $quantityScu, 2).' SCU';
        }

        $quantity = $input['quantity'] ?? null;

        if (! is_numeric($quantity)) {
            return null;
        }

        $normalizedQuantity = (float) $quantity;
        $quantityLabel = fmod($normalizedQuantity, 1.0) === 0.0
            ? (string) (int) $normalizedQuantity
            : rtrim(rtrim(Format::number($normalizedQuantity, 2), '0'), '.');

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
}
