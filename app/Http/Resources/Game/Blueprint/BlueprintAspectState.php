<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Blueprint;

use App\Models\Game\Commodity\Commodity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @internal
 *
 * Builds the interactive aspect tree from normalized requirement groups.
 * Receives a URL-generation callable to avoid coupling to the resource hierarchy.
 */
final class BlueprintAspectState
{
    /**
     * @param  Closure(string, array<string, string>, Request): string  $makeUrl
     * @param  Collection<string, Commodity>  $ingredients  Loaded ingredients keyed by UUID, with rawVersions eager-loaded.
     */
    public function __construct(
        private readonly Closure $makeUrl,
        private readonly Collection $ingredients = new Collection,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $requirementGroups
     * @return array{aspects: array<int, array<string, mixed>>, aspect_groups: array<int, array<string, mixed>>, has_interactive_aspects: bool}
     */
    public function build(array $requirementGroups, ?Request $request = null): array
    {
        $aspects = [];

        foreach ($requirementGroups as $group) {
            $groupName = $this->resolveRequirementLabel($group['name'] ?? null, $group['key'] ?? null, 'Aspect');
            $groupKey = $group['key'] ?? null;
            $groupRequiredCount = is_numeric($group['required_count'] ?? null)
                ? (int) $group['required_count']
                : null;
            $groupChildren = is_array($group['children'] ?? null) ? $group['children'] : [];
            $groupLeafOptionCount = $this->countRenderableLeaves($groupChildren);
            $groupModifiers = is_array($group['modifiers'] ?? null) ? $group['modifiers'] : [];
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
                    $request,
                ),
            ];
        }

        $hasInteractiveAspects = false;

        foreach ($aspects as $aspectIndex => $aspect) {
            $modifiers = is_array($aspect['modifiers'] ?? null) ? $aspect['modifiers'] : [];
            $sliderMin = max(0, (int) ($aspect['input']['min_quality'] ?? 0));
            $sliderMax = 1000;
            $hasModifiers = $modifiers !== [];
            $hasDynamicModifiers = false;

            foreach ($modifiers as $modifier) {
                $qualityMin = $modifier['quality_range']['min'] ?? null;
                $qualityMax = $modifier['quality_range']['max'] ?? null;
                $atMinQuality = $modifier['modifier_range']['at_min_quality'] ?? null;
                $atMaxQuality = $modifier['modifier_range']['at_max_quality'] ?? null;

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
            $selectionKey = $selectionGroup !== null && is_string($selectionGroup['key'] ?? null)
                ? trim((string) $selectionGroup['key'])
                : '';
            $selectionKey = $selectionKey !== '' ? $selectionKey : '__aspect_'.$aspectIndex;
            $optionCount = $selectionGroup !== null && is_numeric($selectionGroup['option_count'] ?? null)
                ? max(1, (int) $selectionGroup['option_count'])
                : 1;
            $requiredSelectionCount = $selectionGroup !== null && is_numeric($selectionGroup['required_count'] ?? null)
                ? max(1, min((int) $selectionGroup['required_count'], $optionCount))
                : 1;
            $isChoiceGroup = $selectionGroup !== null && $requiredSelectionCount < $optionCount;

            if (! array_key_exists($selectionKey, $aspectGroups)) {
                $selectionGroupName = $isChoiceGroup
                    ? $this->resolveRequirementLabel($selectionGroup['name'] ?? null, $selectionGroup['key'] ?? null, 'Input set')
                    : (is_string($aspect['name'] ?? null) ? $aspect['name'] : 'Aspect');
                $selectionGroupDisplayName = $isChoiceGroup && $this->isGenericSelectionGroup(
                    $selectionGroup['name'] ?? null,
                    $selectionGroup['key'] ?? null,
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
                    'aspect_indexes' => [],
                ];
            }

            $isSelected = $aspectGroups[$selectionKey]['selected_count'] < $requiredSelectionCount;
            $aspects[$aspectIndex]['is_selected'] = $isSelected;
            $aspectGroups[$selectionKey]['selected_count'] += $isSelected ? 1 : 0;
            $aspectGroups[$selectionKey]['aspect_indexes'][] = $aspectIndex;
        }

        return [
            'aspects' => array_values($aspects),
            'aspect_groups' => array_values($aspectGroups),
            'has_interactive_aspects' => $hasInteractiveAspects,
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
        ?Request $request = null,
    ): array {
        $aspects = [];

        foreach ($nodes as $node) {
            $kind = (string) ($node['kind'] ?? '');
            $nodeModifiers = is_array($node['modifiers'] ?? null) ? $node['modifiers'] : [];
            $combinedModifiers = [...$inheritedModifiers, ...$nodeModifiers];

            if ($kind === 'group') {
                $nestedName = $node['name'] ?? null;
                $nestedKey = $node['key'] ?? null;
                $nestedRequiredCount = $node['required_count'] ?? null;
                $children = is_array($node['children'] ?? null) ? $node['children'] : [];
                $resolvedAspectName = $this->resolveRequirementLabel($nestedName, $nestedKey, $aspectName ?? 'Aspect');
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
                        $request,
                    ),
                ];

                continue;
            }

            $inputName = $node['name'] ?? 'Unknown input';
            $inputUuid = $node['uuid'] ?? null;

            $aspects[] = [
                'key' => $aspectKey ?? ($node['key'] ?? Str::slug((string) $inputName)),
                'name' => $aspectName ?? $inputName,
                'required_count' => $requiredCount,
                'selection_group' => $selectionGroup,
                'input' => [
                    'kind' => $kind !== '' ? $kind : 'input',
                    'uuid' => $inputUuid,
                    'name' => $inputName,
                    'quantity' => $node['quantity'] ?? null,
                    'quantity_scu' => $node['quantity_scu'] ?? null,
                    'min_quality' => is_numeric($node['min_quality'] ?? null) ? (int) $node['min_quality'] : 0,
                    'web_url' => $this->resolveAspectInputWebUrl($kind, $inputUuid, $request),
                ],
                'modifiers' => $combinedModifiers,
            ];
        }

        return $aspects;
    }

    private function resolveAspectInputWebUrl(string $kind, mixed $inputUuid, ?Request $request): ?string
    {
        if ($request === null || ! is_string($inputUuid) || ! Str::isUuid($inputUuid)) {
            return null;
        }

        if ($kind === 'resource') {
            $oreUuid = $this->ingredients->get($inputUuid)?->rawVersions->first()?->uuid ?? $inputUuid;

            return ($this->makeUrl)(
                'web.commodities.show',
                ['identifier' => $oreUuid],
                $request,
            );
        }

        if ($kind === 'item') {
            return ($this->makeUrl)(
                'web.items.show',
                ['item' => $inputUuid],
                $request,
            );
        }

        return null;
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

            if (($node['kind'] ?? '') === 'group') {
                $children = is_array($node['children'] ?? null) ? $node['children'] : [];
                $count += $this->countRenderableLeaves($children);

                continue;
            }

            $count++;
        }

        return $count;
    }

    private function resolveRequirementLabel(?string $name, ?string $key, string $fallback = 'Aspect'): string
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

    private function isGenericSelectionGroup(?string $name, ?string $key): bool
    {
        $normalizedKey = is_string($key) ? Str::upper(trim($key)) : '';
        $resolvedName = Str::upper($this->resolveRequirementLabel($name, $key, ''));

        return in_array($normalizedKey, ['ASPECT', 'ASPECTS'], true)
            || in_array($resolvedName, ['ASPECT', 'ASPECTS'], true);
    }
}
