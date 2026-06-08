<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Concerns;

use App\Http\Resources\Game\Concerns\NormalizesValues;
use App\Models\Game\Item;
use Illuminate\Support\Arr;

trait ProcessesHardpointData
{
    use NormalizesValues;

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
        // Pre 4.8 ScDataDumper format
        $source = Arr::get($this->resource, 'CompatibleTypes') ?? Arr::get($this->resource, 'ItemTypes', []);

        if ($source === null || $source === []) {
            return [];
        }

        return array_map(static fn ($type) => [
            'type' => Arr::get($type, 'Type'),
            'sub_types' => Arr::get($type, 'SubTypes', []),
        ], $source);
    }

    protected function shouldIncludeChildren(): bool
    {
        return Arr::has($this->resource, 'Loadout');
    }

    protected function getChildrenArray(): array
    {
        return Arr::get($this->resource, 'Loadout', []);
    }

    protected function buildPortTags(): ?array
    {
        return self::normalizeTagList(Arr::get($this->resource, 'PortTags'));
    }

    protected function extractTypeAndSubtype(): array
    {
        [$type, $subtype] = explode('.', Arr::get($this->resource, 'Type', '.'));

        return [$type, $subtype];
    }
}
