<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
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
    ) {
        parent::__construct($resource);
    }

    public function mapHaulingOrders(mixed $data, Request $request): ?array
    {
        $orders = Arr::get($data, 'HaulingOrders');

        if (! is_array($orders) || empty($orders)) {
            return null;
        }

        return $this->mapHaulingOrderEntries($orders, $request);
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
                $mapped = [
                    'kind' => $kind,
                    'name' => $entry['Name'] ?? null,
                    'uuid' => $uuid,
                    'items' => collect($entry['Items'] ?? [])->map(function (array $item) use ($kind, $request): array {
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
