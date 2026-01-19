<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Concerns;

use App\Models\Game\Item;
use Illuminate\Support\Arr;

trait ProcessesHardpointData
{
    protected function loadEquippedItem(): ?Item
    {
        $hasEquippedItem = Arr::has($this->resource, 'UUID');

        if (! $hasEquippedItem) {
            return null;
        }

        $uuid = Arr::get($this->resource, 'UUID', []);

        if (! is_string($uuid) || $uuid === '') {
            return null;
        }

        return $this->loadItemForVersion($uuid);
    }

    protected function extractHealth(?Item $resolvedItem): ?float
    {
        if ($resolvedItem === null) {
            return null;
        }

        return $this->extractFromStdItem($resolvedItem->data?->first(), 'Durability.Health');
    }

    protected function buildCompatibleTypes(): array
    {
        return collect(Arr::get($this->resource, 'ItemTypes', []))
            ->map(static function ($type) {
                return [
                    'type' => Arr::get($type, 'Type'),
                    'sub_types' => Arr::get($type, 'SubTypes', []),
                ];
            })
            ->toArray();
    }

    protected function shouldIncludeChildren(): bool
    {
        return Arr::has($this->resource, 'Loadout');
    }

    protected function getChildrenArray(): array
    {
        return Arr::get($this->resource, 'Loadout', []);
    }

    protected function extractTypeAndSubtype(): array
    {
        [$type, $subtype] = explode('.', Arr::get($this->resource, 'Type', '.'));

        return [$type, $subtype];
    }
}
