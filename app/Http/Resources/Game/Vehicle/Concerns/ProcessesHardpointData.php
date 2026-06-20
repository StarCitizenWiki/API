<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Concerns;

use App\Http\Resources\Game\Concerns\NormalizesValues;
use App\Models\Game\Item;

trait ProcessesHardpointData
{
    use NormalizesValues;

    protected function loadEquippedItem(): ?Item
    {
        $uuid = $this->resource['UUID'] ?? null;

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

        // Per-port hot path: inline to skip the dotted-path walker.
        return $resolvedItem->data?->first()?->data['stdItem']['Durability']['Health'] ?? null;
    }

    protected function buildCompatibleTypes(): array
    {
        // Pre 4.8 ScDataDumper format
        $source = $this->resource['CompatibleTypes'] ?? $this->resource['ItemTypes'] ?? [];

        if ($source === null || $source === []) {
            return [];
        }

        return array_map(static fn (array $type): array => [
            'type' => $type['Type'] ?? null,
            'sub_types' => $type['SubTypes'] ?? [],
        ], $source);
    }

    protected function shouldIncludeChildren(): bool
    {
        return isset($this->resource['Loadout']);
    }

    protected function getChildrenArray(): array
    {
        return $this->resource['Loadout'] ?? [];
    }

    protected function buildPortTags(): ?array
    {
        return self::normalizeTagList($this->resource['PortTags'] ?? null);
    }

    protected function extractTypeAndSubtype(): array
    {
        [$type, $subtype] = explode('.', $this->resource['Type'] ?? '.');

        return [$type, $subtype];
    }
}
