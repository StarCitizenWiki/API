<?php

declare(strict_types=1);

namespace App\Support\Starmap;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class StarmapLocationShowViewData
{
    private const int INITIAL_CHILDREN_PER_GROUP = 6;

    /**
     * @param  array<string, mixed>  $location
     * @return array{
     *     childCount: int,
     *     childTypeCount: int,
     *     childGroups: array<int, array{
     *         key: string,
     *         type_name: string,
     *         count: int,
     *         visible_children: array<int, array{
     *             uuid: string|null,
     *             name: string,
     *             type_name: string|null,
     *             type_classification: string|null,
     *             url: string|null,
     *             testid: string|null,
     *             highlights: array<int, array{label: string, variant: string}>
     *         }>,
     *         overflow_children: array<int, array{
     *             uuid: string|null,
     *             name: string,
     *             type_name: string|null,
     *             type_classification: string|null,
     *             url: string|null,
     *             testid: string|null,
     *             highlights: array<int, array{label: string, variant: string}>
     *         }>
     *     }>,
     * }
     */
    public function build(array $location): array
    {
        $children = $this->normalizeChildren(Arr::get($location, 'children', []));

        $childGroups = $this->buildChildGroups($children);

        return [
            'childCount' => count($children),
            'childTypeCount' => count($childGroups),
            'childGroups' => $childGroups,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @return array<int, array{
     *     key: string,
     *     type_name: string,
     *     count: int,
     *     visible_children: array<int, array{uuid: string|null, name: string, type_name: string|null, type_classification: string|null, url: string|null, testid: string|null, highlights: array<int, array{label: string, variant: string}>}>,
     *     overflow_children: array<int, array{uuid: string|null, name: string, type_name: string|null, type_classification: string|null, url: string|null, testid: string|null, highlights: array<int, array{label: string, variant: string}>}>
     * }>
     */
    private function buildChildGroups(array $children): array
    {
        /** @var Collection<int, Collection<int, array<string, mixed>>> $childrenByType */
        $childrenByType = collect($children)
            ->groupBy(fn (array $child): string => Arr::get($child, 'type_name', 'Unknown Type'))
            ->sortKeys()
            ->map(fn (Collection $group): Collection => $group->sortBy(
                fn (array $child): string => mb_strtolower((string) ($child['name'] ?? ''))
            )->values());

        return $childrenByType
            ->map(function (Collection $group, string $typeName): array {
                $preparedChildren = $group
                    ->map(fn (array $child): array => $this->buildChildCard($child))
                    ->values();

                return [
                    'key' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $typeName) ?: 'unknown-type'),
                    'type_name' => $typeName,
                    'count' => $preparedChildren->count(),
                    'visible_children' => $preparedChildren->take(self::INITIAL_CHILDREN_PER_GROUP)->all(),
                    'overflow_children' => $preparedChildren->slice(self::INITIAL_CHILDREN_PER_GROUP)->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $child
     * @return array{
     *     uuid: string|null,
     *     name: string,
     *     type_name: string|null,
     *     type_classification: string|null,
     *     url: string|null,
     *     testid: string|null,
     *     highlights: array<int, array{label: string, variant: string}>
     * }
     */
    private function buildChildCard(array $child): array
    {
        $childUuid = Arr::get($child, 'uuid');
        $amenityLabels = collect(Arr::get($child, 'amenity_labels', []))
            ->filter()
            ->unique()
            ->values();
        $visibleAmenityLabels = $amenityLabels->take(3)->all();
        $additionalAmenityCount = $amenityLabels->count() - count($visibleAmenityLabels);
        $respawnLocationType = Arr::get($child, 'respawn_location_type');

        $highlights = array_values(array_filter([
            $respawnLocationType !== null && $respawnLocationType !== 'None'
                ? ['label' => 'Respawn: '.$respawnLocationType, 'variant' => 'badge-ghost']
                : null,
            ...array_map(
                static fn (string $amenityLabel): array => ['label' => $amenityLabel, 'variant' => 'badge-outline'],
                $visibleAmenityLabels,
            ),
            $additionalAmenityCount > 0
                ? ['label' => '+'.$additionalAmenityCount, 'variant' => 'badge-outline']
                : null,
        ]));

        return [
            'uuid' => $childUuid,
            'name' => Arr::get($child, 'name', 'Unknown child location'),
            'type_name' => Arr::get($child, 'type_name'),
            'type_classification' => Arr::get($child, 'type_classification'),
            'url' => Arr::get($child, 'web_url'),
            'testid' => $childUuid !== null ? 'starmap-location-child-link-'.$childUuid : null,
            'highlights' => $highlights,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeChildren(mixed $children): array
    {
        if (! is_array($children)) {
            return [];
        }

        return array_values(array_filter($children, static fn (mixed $child): bool => is_array($child)));
    }
}
