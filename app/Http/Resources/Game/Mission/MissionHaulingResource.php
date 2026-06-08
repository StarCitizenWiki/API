<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Services\TagItemResolverService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_hauling_order',
    title: 'Mission Hauling Order',
    properties: [
        new OA\Property(property: 'kind', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_hauling_order_item')
        ),
        new OA\Property(property: 'max_scu', type: 'integer', nullable: true),
        new OA\Property(property: 'min_scu', type: 'integer', nullable: true),
        new OA\Property(property: 'max_amount', type: 'integer', nullable: true),
        new OA\Property(property: 'min_amount', type: 'integer', nullable: true),
        new OA\Property(property: 'max_container_size', type: 'integer', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
        new OA\Property(
            property: 'or_options',
            type: 'array',
            items: new OA\Items(
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/mission_hauling_order')
            ),
            nullable: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_hauling_order_item',
    title: 'Mission Hauling Order Item',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
class MissionHaulingResource extends AbstractBaseResource
{
    /**
     * @param  Closure(string, array<string, string>, Request): string  $makeApiUrl
     * @param  Closure(string, array<string, string>, Request): string  $makeWebUrl
     */
    public function __construct(
        $resource,
        private readonly Closure $makeApiUrl,
        private readonly Closure $makeWebUrl,
        private readonly ?TagItemResolverService $tagResolver = null,
        private readonly ?int $gameVersionId = null,
    ) {
        parent::__construct($resource);
    }

    /**
     * Build hauling orders
     * Some missions define hauling items as tag searches, resolve them here
     */
    public function mapHaulingOrders(mixed $data, Request $request): ?array
    {
        $orders = Arr::get($data, 'HaulingOrders');

        $result = [];

        if (is_array($orders) && ! empty($orders)) {
            $result = $this->mapHaulingOrderEntries($orders, $request);
        }

        $tagOrder = $this->mapSyntheticHaulingOrders($data, $request);
        if ($tagOrder !== null) {
            $result = array_merge($result, $tagOrder);
        }

        return $result !== [] ? $result : null;
    }

    /**
     * Generate synthetic hauling orders from ItemCounts.TagSearchTerms.
     *
     * Each tag search term group becomes one hauling order containing all matching items.
     */
    private function mapSyntheticHaulingOrders(mixed $data, Request $request): ?array
    {
        $tagTerms = Arr::get($data, 'ItemCounts.TagSearchTerms');

        if (! is_array($tagTerms) || $tagTerms === [] || $this->tagResolver === null || $this->gameVersionId === null) {
            return null;
        }

        $itemCounts = Arr::get($data, 'ItemCounts');
        $maxItems = $itemCounts['MaxItems'] ?? null;
        $minItems = $itemCounts['MinItems'] ?? null;

        $groups = [];
        foreach ($tagTerms as $term) {
            $positiveUuids = collect($term['PositiveTags'] ?? [])
                ->pluck('UUID')
                ->filter()
                ->values()
                ->all();

            $negativeUuids = collect($term['NegativeTags'] ?? [])
                ->pluck('UUID')
                ->filter(fn (?string $uuid): bool => $uuid !== null && $uuid !== '00000000-0000-0000-0000-000000000000')
                ->values()
                ->all();

            if ($positiveUuids !== [] || $negativeUuids !== []) {
                $groups[] = ['positive' => $positiveUuids, 'negative' => $negativeUuids];
            }
        }

        if ($groups === []) {
            return null;
        }

        $tagNames = collect($tagTerms)
            ->flatMap(fn (array $term): array => array_map(
                static fn (array $tag): ?string => $tag['Name'] ?? null,
                $term['PositiveTags'] ?? [],
            ))
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        $items = $this->tagResolver->resolveItems($groups, $this->gameVersionId, ['item']);

        if ($items->isEmpty()) {
            return null;
        }

        $orderItems = $items->map(function ($itemData) use ($request): array {
            $uuid = $itemData->item?->uuid;

            return [
                'name' => $itemData->name,
                'uuid' => $uuid,
                'link' => $uuid !== null
                    ? ($this->makeApiUrl)('items.show', ['identifier' => $uuid], $request)
                    : null,
                'web_url' => $uuid !== null
                    ? ($this->makeWebUrl)('web.items.show', ['item' => $itemData->item->slug ?? $uuid], $request)
                    : null,
            ];
        })->values()->all();

        return [
            [
                'kind' => 'TagMatch',
                'name' => $tagNames !== '' ? "Items matching {$tagNames}" : null,
                'uuid' => null,
                'items' => $orderItems,
                'max_scu' => null,
                'min_scu' => null,
                'max_amount' => $maxItems,
                'min_amount' => $minItems,
                'max_container_size' => null,
                'link' => null,
                'web_url' => null,
            ],
        ];
    }

    public function mapHaulingOrderEntries(array $entries, Request $request): array
    {
        $result = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $kind = $entry['Kind'] ?? $entry['ItemKind'] ?? null;

            if ($kind === 'Or') {
                $mapped = [
                    'kind' => $kind,
                    'or_options' => collect($entry['OrOptions'] ?? [])
                        ->map(fn (array $group): array => $this->mapHaulingOrderEntries($group, $request))
                        ->values()
                        ->all(),
                ];
            } else {
                $uuid = $entry['UUID'] ?? null;
                $entryItems = $entry['Items'] ?? [];

                $mapped = [
                    'kind' => $kind,
                    'name' => $entry['Name'] ?? null,
                    'uuid' => $uuid,
                    'items' => collect($entryItems)->map(function (array $item) use ($kind, $request): array {
                        $itemUuid = $item['UUID'] ?? $item['ItemUUID'] ?? null;

                        return [
                            'name' => $item['Name'] ?? null,
                            'uuid' => $itemUuid,
                            'link' => $this->haulingLink($itemUuid, $kind, $request),
                            'web_url' => $this->haulingWebUrl($itemUuid, $kind, $request),
                        ];
                    })->values()->all(),
                    'max_scu' => max((int) ($entry['MinScu'] ?? 0), (int) ($entry['MaxScu'] ?? 0)) ?: null,
                    'min_scu' => min((int) ($entry['MinScu'] ?? 0), (int) ($entry['MaxScu'] ?? 0)) ?: null,
                    'max_amount' => max((int) ($entry['MinAmount'] ?? 0), (int) ($entry['MaxAmount'] ?? 0)) ?: null,
                    'min_amount' => min((int) ($entry['MinAmount'] ?? 0), (int) ($entry['MaxAmount'] ?? 0)) ?: null,
                    'max_container_size' => $entry['MaxContainerSize'] ?? null,
                    'link' => $this->haulingLink($uuid, $kind, $request),
                    'web_url' => $this->haulingWebUrl($uuid, $kind, $request),
                ];
            }

            $result[] = $mapped;
        }

        return $result;
    }

    private function haulingLink(?string $uuid, ?string $kind, Request $request): ?string
    {
        if ($uuid === null) {
            return null;
        }

        if ($kind === 'Resource') {
            return ($this->makeApiUrl)(
                'commodities.show',
                ['commodity' => $uuid],
                $request,
            );
        }

        if ($kind === 'Entity' || $kind === 'Entities' || $kind === 'MissionItem') {
            return ($this->makeApiUrl)(
                'items.show',
                ['identifier' => $uuid],
                $request,
            );
        }

        return null;
    }

    private function haulingWebUrl(?string $uuid, ?string $kind, Request $request): ?string
    {
        if ($uuid === null) {
            return null;
        }

        if ($kind === 'Resource') {
            return ($this->makeWebUrl)(
                'web.commodities.show',
                ['identifier' => $uuid],
                $request,
            );
        }

        if ($kind === 'Entity' || $kind === 'Entities' || $kind === 'MissionItem') {
            return ($this->makeWebUrl)(
                'web.items.show',
                ['item' => $uuid],
                $request,
            );
        }

        return null;
    }
}
