<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Concerns;

use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;
use Illuminate\Support\Arr;

/**
 * Trait for standardized JSON data extraction from ItemData and VehicleData models.
 * Ensures consistent handling of null values, empty arrays, and nested structures.
 */
trait ExtractsJsonData
{
    /**
     * Extract a value from stdItem JSON structure.
     *
     * @param  string  $path  Dot notation path (e.g., 'Ports', 'HeatConnection.ThermalEnergyBase')
     * @param  mixed  $default  Default value if path not found
     */
    protected function extractFromStdItem(ItemData $itemData, string $path, mixed $default = null): mixed
    {
        return Arr::get($itemData->data, "stdItem.{$path}", $default);
    }

    /**
     * Extract a value from vehicle JSON payload.
     *
     * @param  string  $path  Dot notation path
     * @param  mixed  $default  Default value if path not found
     */
    protected function extractFromVehicleJson(VehicleData $vehicleData, string $path, mixed $default = null): mixed
    {
        return Arr::get($vehicleData->data, $path, $default);
    }

    /**
     * Normalize array handling to match v2 behavior.
     *
     * V2 behavior: Empty arrays are returned as empty arrays, not null.
     * V3 behavior: Should match v2 - empty arrays remain empty arrays.
     * Only return null if the value is actually absent from the JSON.
     */
    protected function normalizeArray(mixed $value): ?array
    {
        // If explicitly null or not an array, return null
        if ($value === null || ! is_array($value)) {
            return null;
        }

        // Empty arrays should remain as empty arrays to match v2
        return $value;
    }

    /**
     * Extract a specification from stdItem with type checking.
     * Returns null if the specification doesn't exist or is empty.
     *
     * @param  string  $type  Specification type (e.g., 'Shield', 'Weapon', 'PowerPlant')
     */
    protected function extractSpecification(ItemData $itemData, string $type): ?array
    {
        $spec = Arr::get($itemData->data, "stdItem.{$type}");

        if (! is_array($spec) || $spec === []) {
            return null;
        }

        return $spec;
    }

    /**
     * Extract entity tags from ItemData.
     */
    protected function extractEntityTags(ItemData $itemData): array
    {
        $tags = Arr::get($itemData->data, 'entity_tags', []);

        return is_array($tags) ? $tags : [];
    }

    /**
     * Extract ports from stdItem.
     */
    protected function extractPorts(ItemData $itemData): array
    {
        $ports = Arr::get($itemData->data, 'stdItem.Ports', []);

        return is_array($ports) ? $ports : [];
    }

    /**
     * Extract a numeric value from stdItem, ensuring it's the correct type.
     */
    protected function extractNumeric(ItemData $itemData, string $path, ?float $default = null): ?float
    {
        $value = Arr::get($itemData->data, "stdItem.{$path}", $default);

        if ($value === null) {
            return null;
        }

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * Extract an integer value from stdItem.
     */
    protected function extractInteger(ItemData $itemData, string $path, ?int $default = null): ?int
    {
        $value = Arr::get($itemData->data, "stdItem.{$path}", $default);

        if ($value === null) {
            return null;
        }

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Extract a string value from stdItem.
     */
    protected function extractString(ItemData $itemData, string $path, ?string $default = null): ?string
    {
        $value = Arr::get($itemData->data, "stdItem.{$path}", $default);

        if ($value === null) {
            return null;
        }

        return is_string($value) ? $value : $default;
    }

    /**
     * Extract a boolean value from stdItem.
     */
    protected function extractBoolean(ItemData $itemData, string $path, ?bool $default = null): ?bool
    {
        $value = Arr::get($itemData->data, "stdItem.{$path}", $default);

        if ($value === null) {
            return null;
        }

        return is_bool($value) ? $value : $default;
    }

    /**
     * Extract raw XML data from ItemData.
     *
     * @param  string  $path  Path within Raw structure
     */
    protected function extractFromRaw(ItemData $itemData, string $path, mixed $default = null): mixed
    {
        return Arr::get($itemData->data, "Raw.{$path}", $default);
    }

    /**
     * Check if a path exists in stdItem.
     */
    protected function hasInStdItem(ItemData $itemData, string $path): bool
    {
        return Arr::has($itemData->data, "stdItem.{$path}");
    }

    /**
     * Extract an array and ensure it's properly typed, filtering out null values if needed.
     *
     * @param  bool  $filterNulls  Whether to filter out null values
     */
    protected function extractArray(ItemData $itemData, string $path, bool $filterNulls = false): array
    {
        $value = Arr::get($itemData->data, "stdItem.{$path}", []);

        if (! is_array($value)) {
            return [];
        }

        if ($filterNulls) {
            return array_filter($value, static fn ($item) => $item !== null);
        }

        return $value;
    }
}
